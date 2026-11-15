<?php

declare(strict_types=1);

namespace NowoTech\PhpStanFrankenPhp\Rule\Worker;

use NowoTech\PhpStanFrankenPhp\Support\AstFinder;
use NowoTech\PhpStanFrankenPhp\Support\DoctrineExprResolver;
use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\NullsafeMethodCall;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Name;
use PhpParser\Node\Stmt\Class_;
use PHPStan\Analyser\Scope;
use PHPStan\Node\InClassNode;
use PHPStan\Reflection\ClassReflection;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleErrorBuilder;

/**
 * Level 2 (worker, opt-in, noisy) — flags a raw `EntityManager::flush()` in a class that never
 * recovers a closed entity manager.
 *
 * A failed flush closes the entity manager. In a long-lived worker every later write through the
 * same manager then fails with "The EntityManager is closed" until something resets it.
 *
 * Heuristic (class level): the class calls `flush()` directly on an entity manager and nowhere
 * calls `isOpen()` on an entity manager, `resetManager()`, a `flush($entityManager)` helper, a
 * method whose name contains `recover`, `reopen`, `ensureOpen` or `resetIfClosed` with an entity
 * manager argument, nor any method of a dependency whose class name contains `Recover`, `Flusher`,
 * `TransactionRunner` or `DeadlockRetry`. Classes named / namespaced like tests, fixtures and
 * migrations, and Symfony Console commands (subclasses of `Command` or classes with `#[AsCommand]`)
 * are skipped: they do not run in the worker. A DDD / CQRS `…\Command\…Handler` is analysed: it
 * runs in a Messenger worker.
 *
 * Class level: methods of anonymous classes are not counted for the outer class, and flushes in
 * trait methods are not seen (the trait's own body is not part of the class node).
 *
 * Enable via `frankenphp.flagFlushWithoutClosedManagerRecovery: true` (not part of
 * `ruleset-worker-load.neon`).
 *
 * @implements Rule<InClassNode>
 */
final class NoFlushWithoutClosedManagerRecoveryRule implements Rule
{
    private const MESSAGE = 'Class %s calls EntityManager::flush() but never recovers a closed entity manager (no isOpen() check, no ManagerRegistry::resetManager(), no recovering helper). A failed flush closes the manager: every later write through it in the same request or message fails, and in a worker without DoctrineBundle\'s resetter it stays closed for later requests too. Check isOpen() and reset the manager through the registry after a failure, or flush through a recovering helper / TransactionRunnerInterface (nowo-tech/transaction-contract).';

    private const RECOVERY_CALL_FRAGMENTS = ['recover', 'reopen', 'ensureopen', 'resetifclosed'];

    private const RECOVERY_CLASS_FRAGMENTS = ['recover', 'flusher', 'transactionrunner', 'deadlockretry'];

    private const SKIP_NAME_SUFFIXES = ['test', 'testcase', 'fixture', 'fixtures', 'migration'];

    private const SKIP_FQCN_FRAGMENTS = ['/tests/', '/test/', '/fixtures/', '/datafixtures/', '/migrations/'];

    private const CONSOLE_COMMAND = 'Symfony\\Component\\Console\\Command\\Command';

    private const AS_COMMAND = 'Symfony\\Component\\Console\\Attribute\\AsCommand';

    public function __construct(
        private readonly bool $enabled = false,
    ) {
    }

    public function getNodeType(): string
    {
        return InClassNode::class;
    }

    public function processNode(Node $node, Scope $scope): array
    {
        $class = $node->getOriginalNode();
        if (!$this->enabled || !$class instanceof Class_ || $class->isAnonymous() || $this->skipped($node->getClassReflection()->getName())
            || $this->isConsoleCommand($class, $node->getClassReflection())
        ) {
            return [];
        }

        $direct = [];
        foreach ($class->getMethods() as $method) {
            $resolver = DoctrineExprResolver::forFunction($scope, $node->getClassReflection(), $method);
            $calls = AstFinder::findAll(
                $method,
                static fn (Node $n): bool => null !== AstFinder::callName($n),
                true,
            );
            foreach ($calls as $call) {
                if ($this->isRecovery($call, $resolver)) {
                    return [];
                }
                if ('direct' === $resolver->flushKind($call)) {
                    $direct[] = $call;
                }
            }
        }

        $errors = [];
        foreach ($direct as $flush) {
            $errors[] = RuleErrorBuilder::message(\sprintf(self::MESSAGE, $node->getClassReflection()->getName()))
                ->identifier('frankenphp.worker.noFlushWithoutClosedManagerRecovery')
                ->line($flush->getStartLine())
                ->build();
        }

        return $errors;
    }

    private function isRecovery(Node $call, DoctrineExprResolver $resolver): bool
    {
        /** @var MethodCall|NullsafeMethodCall|StaticCall $call */
        $name = (string) AstFinder::callName($call);
        if ('resetmanager' === $name || 'helper' === $resolver->flushKind($call)) {
            return true;
        }
        if ($call instanceof StaticCall) {
            return $this->isRecoveryHelper($name, $call, $resolver);
        }
        if ('isopen' === $name) {
            return $resolver->isManager($call->var);
        }
        foreach ($resolver->typeOf($call->var)->getObjectClassNames() as $className) {
            $shortName = strtolower(substr($className, (int) strrpos('\\'.$className, '\\')));
            foreach (self::RECOVERY_CLASS_FRAGMENTS as $fragment) {
                if (str_contains($shortName, $fragment)) {
                    return true;
                }
            }
        }

        return $this->isRecoveryHelper($name, $call, $resolver);
    }

    /**
     * `recover…($entityManager)`, `resetIfClosed($entityManager, …)`, `ensureOpen($entityManager)`….
     */
    private function isRecoveryHelper(string $name, MethodCall|NullsafeMethodCall|StaticCall $call, DoctrineExprResolver $resolver): bool
    {
        foreach (self::RECOVERY_CALL_FRAGMENTS as $fragment) {
            if (str_contains($name, $fragment)) {
                foreach ($call->args as $arg) {
                    if ($arg instanceof Arg && $resolver->isManager($arg->value)) {
                        return true;
                    }
                }
            }
        }

        return false;
    }

    private function skipped(string $fqcn): bool
    {
        $lower = strtolower($fqcn);
        $short = substr($lower, (int) strrpos('\\'.$lower, '\\'));
        foreach (self::SKIP_NAME_SUFFIXES as $suffix) {
            if (str_ends_with($short, $suffix)) {
                return true;
            }
        }
        $path = '/'.str_replace('\\', '/', $lower).'/';
        foreach (self::SKIP_FQCN_FRAGMENTS as $fragment) {
            if (str_contains($path, $fragment)) {
                return true;
            }
        }

        return false;
    }

    /**
     * A Symfony Console command, from reflection, or from the AST when Symfony is not loadable.
     */
    private function isConsoleCommand(Class_ $class, ClassReflection $reflection): bool
    {
        $extends = $class->extends instanceof Name ? ltrim($class->extends->toString(), '\\') : null;
        if (self::CONSOLE_COMMAND === $extends || $reflection->isSubclassOf(self::CONSOLE_COMMAND)) {
            return true;
        }
        foreach ($class->attrGroups as $group) {
            foreach ($group->attrs as $attribute) {
                if (self::AS_COMMAND === ltrim($attribute->name->toString(), '\\')) {
                    return true;
                }
            }
        }

        return false;
    }
}

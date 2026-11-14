<?php

declare(strict_types=1);

namespace NowoTech\PhpStanFrankenPhp\Rule\Worker;

use NowoTech\PhpStanFrankenPhp\Support\AstFinder;
use NowoTech\PhpStanFrankenPhp\Support\DoctrineExprResolver;
use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr\ArrowFunction;
use PhpParser\Node\Expr\Closure;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\FunctionLike;
use PhpParser\Node\Identifier;
use PhpParser\Node\Stmt\Catch_;
use PhpParser\Node\Stmt\Finally_;
use PHPStan\Analyser\Scope;
use PHPStan\Node\InClassNode;
use PHPStan\Reflection\ClassReflection;
use PHPStan\Rules\IdentifierRuleError;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleErrorBuilder;

/**
 * Level 2 (worker, opt-in) — flags an ORM `flush()` inside a hand-written transaction.
 *
 * `flush()` opens its own transaction; inside `beginTransaction()` … `commit()` or inside a
 * `wrapInTransaction()` / `transactional()` callable it runs at nesting level 2, on a savepoint.
 * After a deadlock the savepoint is gone, the ORM's own rollback fails with 1305, the deadlock is
 * never recognised (so never retried) and the connection stays nested for the worker's next
 * requests.
 *
 * Heuristic: a `flush()` on an entity manager (or a `flush($entityManager)` helper) that comes
 * after a `beginTransaction()` in the same function and before a later `commit()` (or with no
 * `commit()` after it), outside catch / finally blocks; or any such flush in the body of a closure
 * / arrow function passed directly to `wrapInTransaction()` / `transactional()`. Classes whose
 * name or interfaces contain `TransactionRunner` are skipped: the runner is where the
 * transaction belongs.
 *
 * Enable via `frankenphp.flagFlushInHandWrittenTransaction: true` or `ruleset-worker-load.neon`.
 *
 * @implements Rule<InClassNode>
 */
final class NoFlushInHandWrittenTransactionRule implements Rule
{
    private const MESSAGE = 'EntityManager::flush() inside %s runs on a nested transaction (a savepoint): after a deadlock the savepoint is gone, the ORM\'s own rollback fails with 1305, the deadlock is never retried and the connection stays nested for the worker\'s next requests. Run the unit of work through TransactionRunnerInterface::run() (one flush at the outer level, connection repaired on failure), or move the flush out of the transaction%s.';

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
        if (!$this->enabled || $this->isRunner($node->getClassReflection())) {
            return [];
        }

        $errors = [];
        $reported = [];
        foreach ($node->getOriginalNode()->getMethods() as $method) {
            $resolver = DoctrineExprResolver::forFunction($scope, $node->getClassReflection(), $method);
            foreach (AstFinder::findAll($method, static fn (Node $n): bool => $n instanceof FunctionLike, true) as $function) {
                if ($function instanceof FunctionLike) {
                    $this->checkBeginCommit($function->getStmts() ?? [], $resolver, $reported, $errors);
                }
            }
            $wrappers = AstFinder::findAll(
                $method,
                static fn (Node $n): bool => $resolver->isTransactionCall($n, ['wrapintransaction', 'transactional']),
                true,
            );
            foreach ($wrappers as $wrapper) {
                /** @var MethodCall $wrapper */
                $callable = ($wrapper->args[0] ?? null) instanceof Arg ? $wrapper->args[0]->value : null;
                if ($callable instanceof Closure || $callable instanceof ArrowFunction) {
                    $name = $wrapper->name instanceof Identifier ? $wrapper->name->toString() : 'wrapInTransaction';
                    $this->report($this->flushes($callable->getStmts(), $resolver), 'a '.$name.'() callable', DoctrineExprResolver::KIND_CONNECTION === $resolver->kindOf($wrapper->var) ? '' : ' (EntityManager::'.$name.'() already flushes after the callable)', $reported, $errors);
                }
            }
        }

        return $errors;
    }

    /**
     * @param array<Node\Stmt>          $body
     * @param array<int, true>          $reported
     * @param list<IdentifierRuleError> $errors
     */
    private function checkBeginCommit(array $body, DoctrineExprResolver $resolver, array &$reported, array &$errors): void
    {
        $begins = AstFinder::findAll($body, static fn (Node $n): bool => $resolver->isTransactionCall($n, ['begintransaction']));
        if ([] === $begins) {
            return;
        }
        $commits = AstFinder::findAll($body, static fn (Node $n): bool => $resolver->isTransactionCall($n, ['commit']));
        $inside = [];
        foreach ($this->flushes($body, $resolver) as $flush) {
            foreach ($begins as $begin) {
                if ($flush->getStartFilePos() > $begin->getEndFilePos() && $this->beforeCommit($flush, $begin, $commits)) {
                    $inside[] = $flush;
                    break;
                }
            }
        }
        $this->report($inside, 'a beginTransaction()…commit() block', '', $reported, $errors);
    }

    /**
     * @param list<Node> $commits
     */
    private function beforeCommit(Node $flush, Node $begin, array $commits): bool
    {
        $after = array_filter($commits, static fn (Node $c): bool => $c->getStartFilePos() > $begin->getEndFilePos());

        return [] === $after || [] !== array_filter($after, static fn (Node $c): bool => $c->getStartFilePos() > $flush->getEndFilePos());
    }

    /**
     * Flush calls under `$body`, not inside catch / finally blocks nor nested functions.
     *
     * @param array<Node> $body
     *
     * @return list<Node>
     */
    private function flushes(array $body, DoctrineExprResolver $resolver): array
    {
        $excluded = [];
        foreach (AstFinder::findAll($body, static fn (Node $n): bool => $n instanceof Catch_ || $n instanceof Finally_) as $handler) {
            foreach (AstFinder::findAll($handler, static fn (Node $n): bool => true) as $inner) {
                $excluded[spl_object_id($inner)] = true;
            }
        }

        return array_values(array_filter(
            AstFinder::findAll($body, static fn (Node $n): bool => null !== $resolver->flushKind($n)),
            static fn (Node $n): bool => !isset($excluded[spl_object_id($n)]),
        ));
    }

    /**
     * @param list<Node>                $flushes
     * @param array<int, true>          $reported
     * @param list<IdentifierRuleError> $errors
     */
    private function report(array $flushes, string $where, string $hint, array &$reported, array &$errors): void
    {
        foreach ($flushes as $flush) {
            if (isset($reported[spl_object_id($flush)])) {
                continue;
            }
            $reported[spl_object_id($flush)] = true;
            $errors[] = RuleErrorBuilder::message(\sprintf(self::MESSAGE, $where, $hint))
                ->identifier('frankenphp.worker.noFlushInHandWrittenTransaction')
                ->line($flush->getStartLine())
                ->build();
        }
    }

    private function isRunner(ClassReflection $class): bool
    {
        $names = array_merge([$class->getName()], array_keys($class->getInterfaces()));
        foreach ($names as $name) {
            if (str_contains(strtolower($name), 'transactionrunner')) {
                return true;
            }
        }

        return false;
    }
}

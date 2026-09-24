<?php

declare(strict_types=1);

namespace NowoTech\PhpStanFrankenPhp\Rule\Worker;

use PhpParser\Node;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\AssignOp;
use PhpParser\Node\Expr\PostDec;
use PhpParser\Node\Expr\PostInc;
use PhpParser\Node\Expr\PreDec;
use PhpParser\Node\Expr\PreInc;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use PHPStan\Analyser\Scope;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleErrorBuilder;

/**
 * Level 2 (worker, opt-in) — flags classes that mutate `$this->…` outside
 * construct/reset without implementing Symfony ResetInterface.
 *
 * Targets FrankenPHP worker with FRANKENPHP_RESET_KERNEL unset/false: the kernel
 * instance is reused; only services that implement ResetInterface (or are tagged
 * kernel.reset) are cleared by services_resetter between requests.
 *
 * Enable via `frankenphp.flagMissingResetInterface: true` or
 * `ruleset-worker-no-kernel-reset.neon`.
 *
 * @implements Rule<Class_>
 */
final class NoMissingResetInterfaceRule implements Rule
{
    private const ALLOWED_METHODS = [
        '__construct',
        '__destruct',
        '__clone',
        '__wakeup',
        '__unserialize',
        'reset',
    ];

    private const SKIP_NAME_SUFFIXES = [
        'entity',
        'dto',
        'vo',
        'valueobject',
        'message',
        'event',
        'command',
        'query',
        'request',
        'response',
        'exception',
        'enum',
        'fixture',
        'factory',
        'builder',
        'test',
        'testcase',
    ];

    public function __construct(
        private readonly bool $enabled = false,
    ) {
    }

    public function getNodeType(): string
    {
        return Class_::class;
    }

    public function processNode(Node $node, Scope $scope): array
    {
        if (!$this->enabled || !$node instanceof Class_) {
            return [];
        }

        if ($node->isAnonymous() || $node->isAbstract() || !$node->namespacedName instanceof Name) {
            return [];
        }

        if ($this->implementsResetInterface($node, $scope)) {
            return [];
        }

        $short = $node->namespacedName->getLast();
        if ($this->shouldSkipByName($short, $node->namespacedName->toString())) {
            return [];
        }

        if (!$this->hasMutableInstanceWriteOutsideLifecycle($node)) {
            return [];
        }

        return [
            RuleErrorBuilder::message(
                \sprintf(
                    'Class %s mutates instance state outside __construct/reset but does not implement Symfony\\Contracts\\Service\\ResetInterface. Under FrankenPHP worker with FRANKENPHP_RESET_KERNEL unset/false the kernel is reused; implement reset() (or keep the service stateless) so services_resetter clears request data.',
                    $node->namespacedName->toString()
                )
            )
                ->identifier('frankenphp.worker.noMissingResetInterface')
                ->build(),
        ];
    }

    private function implementsResetInterface(Class_ $node, Scope $scope): bool
    {
        foreach ($node->implements as $interface) {
            if (!$interface instanceof Name) {
                continue; // @codeCoverageIgnore
            }

            $resolved = ltrim($scope->resolveName($interface), '\\');
            if ('Symfony\\Contracts\\Service\\ResetInterface' === $resolved
                || str_ends_with($resolved, '\\ResetInterface')
            ) {
                return true;
            }
        }

        return false;
    }

    private function shouldSkipByName(string $short, string $fqcn): bool
    {
        $lowerShort = strtolower($short);
        foreach (self::SKIP_NAME_SUFFIXES as $suffix) {
            if ($lowerShort === $suffix || str_ends_with($lowerShort, $suffix)) {
                return true;
            }
        }

        $lowerFqcn = strtolower(str_replace('\\', '/', $fqcn));
        foreach (['/entity/', '/dto/', '/message/', '/event/', '/exception/', '/tests/', '/fixtures/'] as $fragment) {
            if (str_contains($lowerFqcn, $fragment)) {
                return true;
            }
        }

        return false;
    }

    private function hasMutableInstanceWriteOutsideLifecycle(Class_ $node): bool
    {
        foreach ($node->getMethods() as $method) {
            $name = $method->name->toString();
            if (\in_array(strtolower($name), self::ALLOWED_METHODS, true)) {
                continue;
            }

            if ($this->methodWritesThisProperty($method)) {
                return true;
            }
        }

        return false;
    }

    private function methodWritesThisProperty(ClassMethod $method): bool
    {
        if (null === $method->stmts) {
            return false;
        }

        foreach ($method->stmts as $stmt) {
            if ($this->nodeWritesThisProperty($stmt)) {
                return true;
            }
        }

        return false;
    }

    private function nodeWritesThisProperty(Node $node): bool
    {
        if (($node instanceof Assign || $node instanceof AssignOp) && $this->isThisProperty($node->var)) {
            return true;
        }

        if (($node instanceof PreInc || $node instanceof PostInc || $node instanceof PreDec || $node instanceof PostDec)
            && $this->isThisProperty($node->var)
        ) {
            return true;
        }

        foreach ($node->getSubNodeNames() as $name) {
            $sub = $node->{$name};
            if ($sub instanceof Node && $this->nodeWritesThisProperty($sub)) {
                return true;
            }
            if (\is_array($sub)) {
                foreach ($sub as $child) {
                    if ($child instanceof Node && $this->nodeWritesThisProperty($child)) {
                        return true;
                    }
                }
            }
        }

        return false;
    }

    private function isThisProperty(Node $node): bool
    {
        return $node instanceof PropertyFetch
            && $node->var instanceof Variable
            && 'this' === $node->var->name
            && $node->name instanceof Identifier;
    }
}

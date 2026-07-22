<?php

declare(strict_types=1);

namespace NowoTech\PhpStanFrankenPhp\Rule\Worker;

use PhpParser\Node;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\StaticPropertyFetch;
use PhpParser\Node\Stmt\Property;
use PHPStan\Analyser\Scope;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleErrorBuilder;

/**
 * Level 2 (worker) — flags mutable static properties.
 *
 * Static properties persist for the lifetime of the FrankenPHP worker thread.
 * Request-scoped data stored statically leaks between users and grows memory.
 *
 * @implements Rule<Node>
 */
final class NoMutableStaticPropertyRule implements Rule
{
    public function getNodeType(): string
    {
        return Node::class;
    }

    public function processNode(Node $node, Scope $scope): array
    {
        if ($node instanceof Property && $node->isStatic() && !$node->isReadonly()) {
            foreach ($node->props as $prop) {
                return [
                    RuleErrorBuilder::message(
                        \sprintf(
                            'Mutable static property $%s persists across FrankenPHP worker requests. Use instance state with ResetInterface / kernel.reset, a request-scoped service, or a class constant for immutable values (PHP does not allow readonly static properties).',
                            $prop->name->toString()
                        )
                    )
                        ->identifier('frankenphp.worker.noMutableStaticProperty')
                        ->build(),
                ];
            }
        }

        if ($node instanceof Assign && $this->targetsStaticProperty($node->var)) {
            return [
                RuleErrorBuilder::message(
                    'Assignment to a static property persists across FrankenPHP worker requests and can leak state between users. Clear via ResetInterface or avoid static mutable state.'
                )
                    ->identifier('frankenphp.worker.noMutableStaticProperty')
                    ->build(),
            ];
        }

        return [];
    }

    private function targetsStaticProperty(Node\Expr $expr): bool
    {
        if ($expr instanceof StaticPropertyFetch) {
            return true;
        }

        if ($expr instanceof Node\Expr\ArrayDimFetch) {
            return $this->targetsStaticProperty($expr->var);
        }

        return false;
    }
}

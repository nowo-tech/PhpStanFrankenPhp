<?php

declare(strict_types=1);

namespace NowoTech\PhpStanFrankenPhp\Rule\Worker;

use PhpParser\Node;
use PhpParser\Node\Expr\ArrayDimFetch;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\AssignOp;
use PhpParser\Node\Expr\AssignRef;
use PhpParser\Node\Expr\PostDec;
use PhpParser\Node\Expr\PostInc;
use PhpParser\Node\Expr\PreDec;
use PhpParser\Node\Expr\PreInc;
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
 * Reports every property of a static declaration (`static $a, $b;`) and every write to a static
 * property: `=`, `=&`, compound assignments (`.=`, `+=`, `??=`, …) and `++` / `--`.
 *
 * The node type stays {@see Node}: declarations (`Stmt\Property`) and writes (`Expr\*`) share no
 * narrower common parent, and `processNode()` bails out with cheap `instanceof` checks.
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
        if ($node instanceof Property) {
            if (!$node->isStatic() || $node->isReadonly()) {
                return [];
            }

            $errors = [];
            foreach ($node->props as $prop) {
                $errors[] = RuleErrorBuilder::message(
                    \sprintf(
                        'Mutable static property $%s persists across FrankenPHP worker requests. Use instance state with ResetInterface / kernel.reset, a request-scoped service, or a class constant for immutable values (PHP does not allow readonly static properties).',
                        $prop->name->toString()
                    )
                )
                    ->identifier('frankenphp.worker.noMutableStaticProperty')
                    ->line($prop->getStartLine())
                    ->build();
            }

            return $errors;
        }

        if ($this->isWrite($node) && $this->targetsStaticProperty($node->var)) {
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

    /**
     * @phpstan-assert-if-true Assign|AssignRef|AssignOp|PreInc|PostInc|PreDec|PostDec $node
     */
    private function isWrite(Node $node): bool
    {
        return $node instanceof Assign
            || $node instanceof AssignRef
            || $node instanceof AssignOp
            || $node instanceof PreInc
            || $node instanceof PostInc
            || $node instanceof PreDec
            || $node instanceof PostDec;
    }

    private function targetsStaticProperty(Node\Expr $expr): bool
    {
        while ($expr instanceof ArrayDimFetch) {
            $expr = $expr->var;
        }

        return $expr instanceof StaticPropertyFetch;
    }
}

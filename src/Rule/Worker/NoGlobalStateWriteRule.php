<?php

declare(strict_types=1);

namespace NowoTech\PhpStanFrankenPhp\Rule\Worker;

use PhpParser\Node;
use PhpParser\Node\Expr\ArrayDimFetch;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Stmt\Global_;
use PHPStan\Analyser\Scope;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleErrorBuilder;

/**
 * Level 2 (worker) — flags global keyword and $GLOBALS writes.
 *
 * Globals in the worker script / process survive requests and are a common
 * source of cross-request contamination when leaving FPM.
 *
 * @implements Rule<Node>
 */
final class NoGlobalStateWriteRule implements Rule
{
    public function getNodeType(): string
    {
        return Node::class;
    }

    public function processNode(Node $node, Scope $scope): array
    {
        if ($node instanceof Global_) {
            return [
                RuleErrorBuilder::message(
                    'The global keyword shares mutable state across FrankenPHP worker requests. Pass dependencies explicitly or use the service container.'
                )
                    ->identifier('frankenphp.worker.noGlobalStateWrite')
                    ->build(),
            ];
        }

        if ($node instanceof Assign && $this->isGlobalsWrite($node->var)) {
            return [
                RuleErrorBuilder::message(
                    'Writing to $GLOBALS persists across FrankenPHP worker requests. Prefer dependency injection or request attributes.'
                )
                    ->identifier('frankenphp.worker.noGlobalStateWrite')
                    ->build(),
            ];
        }

        return [];
    }

    private function isGlobalsWrite(Node\Expr $expr): bool
    {
        if ($expr instanceof Variable && 'GLOBALS' === $expr->name) {
            return true;
        }

        if ($expr instanceof ArrayDimFetch) {
            return $this->isGlobalsWrite($expr->var);
        }

        return false;
    }
}

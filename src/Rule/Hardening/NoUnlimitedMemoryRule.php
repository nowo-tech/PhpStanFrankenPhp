<?php

declare(strict_types=1);

namespace NowoTech\PhpStanFrankenPhp\Rule\Hardening;

use NowoTech\PhpStanFrankenPhp\Support\NodeHelper;
use PhpParser\Node;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Scalar\String_;
use PHPStan\Analyser\Scope;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleErrorBuilder;

/**
 * Level 3 (hardening) — flags memory_limit = -1 via ini_set.
 *
 * Unbounded memory on a long-lived worker turns leaks into OOM kills.
 *
 * @implements Rule<FuncCall>
 */
final class NoUnlimitedMemoryRule implements Rule
{
    public function getNodeType(): string
    {
        return FuncCall::class;
    }

    public function processNode(Node $node, Scope $scope): array
    {
        if (!$node instanceof FuncCall || !NodeHelper::isFunctionNamedExactly($node, 'ini_set')) {
            return [];
        }

        $key = NodeHelper::argExpr($node, 0);
        $value = NodeHelper::argExpr($node, 1);
        if (null === $key || null === $value) {
            return [];
        }

        if (!$key instanceof String_ || 'memory_limit' !== strtolower($key->value)) {
            return [];
        }

        $unlimited = false;
        if ($value instanceof Node\Scalar\LNumber && -1 === $value->value) {
            $unlimited = true;
        } elseif ($value instanceof String_ && \in_array(strtolower(trim($value->value)), ['-1', '0'], true)) {
            $unlimited = true;
        } elseif ($value instanceof Node\Expr\UnaryMinus && $value->expr instanceof Node\Scalar\LNumber && 1 === $value->expr->value) {
            $unlimited = true;
        }

        if (!$unlimited) {
            return [];
        }

        return [
            RuleErrorBuilder::message(
                'ini_set(\'memory_limit\', -1) removes the memory ceiling. On FrankenPHP workers leaks accumulate until OOM. Set a finite limit and fix leaks / use max_requests.'
            )
                ->identifier('frankenphp.hardening.noUnlimitedMemory')
                ->build(),
        ];
    }
}

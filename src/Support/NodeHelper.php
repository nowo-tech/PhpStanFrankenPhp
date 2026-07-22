<?php

declare(strict_types=1);

namespace NowoTech\PhpStanFrankenPhp\Support;

use PhpParser\Node;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Name;
use PHPStan\Analyser\Scope;

/**
 * Shared helpers for FrankenPHP PHPStan rules.
 */
final class NodeHelper
{
    /**
     * @param list<string> $names
     */
    public static function isFunctionNamed(FuncCall $node, array $names): bool
    {
        if (!$node->name instanceof Name) {
            return false;
        }

        $resolved = strtolower($node->name->toString());

        foreach ($names as $name) {
            if ($resolved === strtolower($name)) {
                return true;
            }
        }

        return false;
    }

    public static function isFunctionNamedExactly(FuncCall $node, string $name): bool
    {
        return self::isFunctionNamed($node, [$name]);
    }

    /**
     * Returns the first argument expression when present.
     */
    public static function firstArgExpr(FuncCall $node): ?Node\Expr
    {
        return self::argExpr($node, 0);
    }

    /**
     * Returns the argument expression at index when it is a concrete Arg.
     */
    public static function argExpr(FuncCall $node, int $index): ?Node\Expr
    {
        if (!isset($node->args[$index]) || !$node->args[$index] instanceof Node\Arg) {
            return null;
        }

        return $node->args[$index]->value;
    }

    /**
     * Whether the expression is an integer/float literal equal to zero (or null const for timeouts).
     */
    public static function isZeroLikeLiteral(Node\Expr $expr): bool
    {
        if ($expr instanceof Node\Expr\ConstFetch) {
            return 'null' === strtolower($expr->name->toString());
        }

        if ($expr instanceof Node\Scalar\LNumber) {
            return 0 === $expr->value;
        }

        if ($expr instanceof Node\Scalar\DNumber) {
            return 0.0 === $expr->value;
        }

        if ($expr instanceof Node\Scalar\String_ && is_numeric($expr->value)) {
            return 0.0 === (float) $expr->value;
        }

        return false;
    }

    public static function isInClass(Scope $scope): bool
    {
        return $scope->isInClass();
    }
}

<?php

declare(strict_types=1);

namespace NowoTech\PhpStanFrankenPhp\Support;

use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\NullsafeMethodCall;
use PhpParser\Node\Expr\NullsafePropertyFetch;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Expr\StaticPropertyFetch;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\FunctionLike;
use PhpParser\Node\Identifier;
use PhpParser\Node\Stmt\ClassLike;

/**
 * Syntactic helpers shared by the worker database / recursion rules: walking a function body
 * without entering nested functions, call names and identifier tokens.
 */
final class AstFinder
{
    /**
     * Every node under `$nodes` (the roots included) accepted by `$filter`. Nested functions,
     * closures, arrow functions and classes are not entered unless `$enterFunctions` is true
     * (the nested node itself is still offered to `$filter`).
     *
     * @param Node|array<mixed>    $nodes  nodes or (nested) lists of nodes
     * @param callable(Node): bool $filter
     *
     * @return list<Node>
     */
    public static function findAll(Node|array $nodes, callable $filter, bool $enterFunctions = false): array
    {
        $found = [];
        $visit = static function (Node $node, bool $isRoot) use (&$visit, &$found, $filter, $enterFunctions): void {
            if ($filter($node)) {
                $found[] = $node;
            }
            if (!$isRoot && !$enterFunctions && ($node instanceof FunctionLike || $node instanceof ClassLike)) {
                return;
            }
            foreach ($node->getSubNodeNames() as $name) {
                $sub = $node->{$name};
                foreach (\is_array($sub) ? $sub : [$sub] as $child) {
                    if ($child instanceof Node) {
                        $visit($child, false);
                    }
                }
            }
        };
        $roots = \is_array($nodes) ? $nodes : [$nodes];
        while ([] !== $roots) {
            $node = array_shift($roots);
            if (\is_array($node)) {
                array_unshift($roots, ...array_values($node));
            } elseif ($node instanceof Node) {
                $visit($node, true);
            }
        }

        return $found;
    }

    /**
     * Calls (method, nullsafe method, static) under `$nodes` whose lower-cased name is one of `$names`.
     *
     * @param Node|array<mixed> $nodes
     * @param list<string>      $names lower-case method names
     *
     * @return list<MethodCall|NullsafeMethodCall|StaticCall>
     */
    public static function calls(Node|array $nodes, array $names, bool $enterFunctions = false): array
    {
        /** @var list<MethodCall|NullsafeMethodCall|StaticCall> $calls */
        $calls = self::findAll(
            $nodes,
            static fn (Node $n): bool => \in_array(self::callName($n), $names, true),
            $enterFunctions,
        );

        return $calls;
    }

    /**
     * Lower-cased name of a method / nullsafe method / static call with a literal name, else null.
     */
    public static function callName(Node $node): ?string
    {
        if (($node instanceof MethodCall || $node instanceof NullsafeMethodCall || $node instanceof StaticCall)
            && $node->name instanceof Identifier
        ) {
            return $node->name->toLowerString();
        }

        return null;
    }

    /**
     * The name that best describes an expression: a variable, property or called method name.
     */
    public static function terminalName(Node $expr): ?string
    {
        if ($expr instanceof Variable && \is_string($expr->name)) {
            return $expr->name;
        }
        if (($expr instanceof PropertyFetch || $expr instanceof NullsafePropertyFetch || $expr instanceof StaticPropertyFetch
            || $expr instanceof MethodCall || $expr instanceof NullsafeMethodCall || $expr instanceof StaticCall)
            && $expr->name instanceof Identifier
        ) {
            return $expr->name->toString();
        }

        return null;
    }

    /**
     * camelCase / snake_case identifier split into lower-case tokens (`childrenOf` → children, of).
     *
     * @return list<string>
     */
    public static function tokens(string $identifier): array
    {
        $parts = preg_split('/(?<=[a-z0-9])(?=[A-Z])|[_\-\s]+/', $identifier);

        return array_values(array_filter(
            array_map('strtolower', false === $parts ? [] : $parts),
            static fn (string $t): bool => '' !== $t,
        ));
    }
}

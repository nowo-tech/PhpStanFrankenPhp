<?php

declare(strict_types=1);

namespace NowoTech\PhpStanFrankenPhp\Rule\Worker;

use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name;
use PHPStan\Analyser\Scope;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleErrorBuilder;

/**
 * Level 2 (worker) — flags classic singleton getInstance() patterns.
 *
 * Singletons keep mutable instance state for the worker lifetime and are a
 * frequent source of "works on FPM, wrong user on worker" bugs.
 *
 * @implements Rule<Node>
 */
final class NoSingletonGetInstanceRule implements Rule
{
    public function getNodeType(): string
    {
        return Node::class;
    }

    public function processNode(Node $node, Scope $scope): array
    {
        if ($node instanceof StaticCall) {
            $method = $node->name instanceof Identifier ? $node->name->toString() : null;
            if (null !== $method && 0 === strcasecmp($method, 'getInstance')) {
                $class = $node->class instanceof Name ? $node->class->toString() : 'class';

                return [
                    RuleErrorBuilder::message(
                        \sprintf(
                            '%s::getInstance() singletons retain state across FrankenPHP worker requests. Prefer container-managed services (and ResetInterface when they hold request data).',
                            $class
                        )
                    )
                        ->identifier('frankenphp.worker.noSingletonGetInstance')
                        ->build(),
                ];
            }
        }

        if ($node instanceof MethodCall && $node->name instanceof Identifier
            && 0 === strcasecmp($node->name->toString(), 'getInstance')) {
            return [
                RuleErrorBuilder::message(
                    'getInstance() singleton access retains state across FrankenPHP worker requests. Prefer container-managed services.'
                )
                    ->identifier('frankenphp.worker.noSingletonGetInstance')
                    ->build(),
            ];
        }

        return [];
    }
}

<?php

declare(strict_types=1);

namespace NowoTech\PhpStanFrankenPhp\Rule\Worker;

use PhpParser\Node;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Stmt\Static_;
use PHPStan\Analyser\Scope;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleErrorBuilder;

/**
 * Level 2 (worker) — flags function/method-level static variables.
 *
 * `static $cache = ...` inside functions retains values between requests on the
 * same worker thread (documented FrankenPHP behaviour).
 *
 * @implements Rule<Static_>
 */
final class NoStaticLocalVariableRule implements Rule
{
    public function getNodeType(): string
    {
        return Static_::class;
    }

    public function processNode(Node $node, Scope $scope): array
    {
        if (!$node instanceof Static_) {
            return [];
        }

        $names = [];
        foreach ($node->vars as $var) {
            if ($var->var instanceof Variable && \is_string($var->var->name)) {
                $names[] = '$'.$var->var->name;
            }
        }

        $label = [] === $names ? 'static local variable' : implode(', ', $names);

        return [
            RuleErrorBuilder::message(
                \sprintf(
                    'Static local variable (%s) retains values across FrankenPHP worker requests. Use a local variable, injected cache service, or ResetInterface-backed state.',
                    $label
                )
            )
                ->identifier('frankenphp.worker.noStaticLocalVariable')
                ->build(),
        ];
    }
}

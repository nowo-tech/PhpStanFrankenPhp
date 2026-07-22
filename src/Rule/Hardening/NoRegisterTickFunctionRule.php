<?php

declare(strict_types=1);

namespace NowoTech\PhpStanFrankenPhp\Rule\Hardening;

use NowoTech\PhpStanFrankenPhp\Support\NodeHelper;
use PhpParser\Node;
use PhpParser\Node\Expr\FuncCall;
use PHPStan\Analyser\Scope;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleErrorBuilder;

/**
 * Level 3 (hardening) — flags register_tick_function().
 *
 * Tick handlers remain registered on the worker process and add overhead /
 * surprising re-entrancy across requests.
 *
 * @implements Rule<FuncCall>
 */
final class NoRegisterTickFunctionRule implements Rule
{
    public function getNodeType(): string
    {
        return FuncCall::class;
    }

    public function processNode(Node $node, Scope $scope): array
    {
        if (!$node instanceof FuncCall || !NodeHelper::isFunctionNamedExactly($node, 'register_tick_function')) {
            return [];
        }

        return [
            RuleErrorBuilder::message(
                'register_tick_function() stays active on the FrankenPHP worker and is rarely safe in request code. Prefer explicit instrumentation or middleware.'
            )
                ->identifier('frankenphp.hardening.noRegisterTickFunction')
                ->build(),
        ];
    }
}

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
 * Level 3 (hardening) — flags set_time_limit(0) / unlimited execution.
 *
 * Unlimited request time lets a stuck handler hold a FrankenPHP worker forever.
 *
 * @implements Rule<FuncCall>
 */
final class NoUnlimitedExecutionTimeRule implements Rule
{
    public function getNodeType(): string
    {
        return FuncCall::class;
    }

    public function processNode(Node $node, Scope $scope): array
    {
        if (!$node instanceof FuncCall || !NodeHelper::isFunctionNamedExactly($node, 'set_time_limit')) {
            return [];
        }

        $arg = NodeHelper::firstArgExpr($node);
        if (null === $arg || !NodeHelper::isZeroLikeLiteral($arg)) {
            return [];
        }

        return [
            RuleErrorBuilder::message(
                'set_time_limit(0) disables the execution deadline and can pin a FrankenPHP worker indefinitely. Use a finite limit aligned with Caddy/FrankenPHP timeouts.'
            )
                ->identifier('frankenphp.hardening.noUnlimitedExecutionTime')
                ->build(),
        ];
    }
}

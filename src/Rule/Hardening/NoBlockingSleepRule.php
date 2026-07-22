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
 * Level 3 (hardening) — flags sleep/usleep/time_nanosleep in request paths.
 *
 * Blocking sleeps waste a FrankenPHP worker slot. Prefer async, queues, or
 * HTTP 202 + background processing.
 *
 * @implements Rule<FuncCall>
 */
final class NoBlockingSleepRule implements Rule
{
    private const FUNCTIONS = [
        'sleep',
        'usleep',
        'time_nanosleep',
        'time_sleep_until',
    ];

    public function getNodeType(): string
    {
        return FuncCall::class;
    }

    public function processNode(Node $node, Scope $scope): array
    {
        if (!$node instanceof FuncCall || !NodeHelper::isFunctionNamed($node, self::FUNCTIONS)) {
            return [];
        }

        $name = $node->name instanceof Node\Name ? $node->name->toString() : 'sleep';

        return [
            RuleErrorBuilder::message(
                \sprintf(
                    '%s() blocks a FrankenPHP worker thread. Avoid sleeping in request handlers; use a queue, retry with backoff outside the hot path, or an async client.',
                    $name
                )
            )
                ->identifier('frankenphp.hardening.noBlockingSleep')
                ->build(),
        ];
    }
}

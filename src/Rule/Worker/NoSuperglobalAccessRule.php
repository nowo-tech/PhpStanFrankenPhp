<?php

declare(strict_types=1);

namespace NowoTech\PhpStanFrankenPhp\Rule\Worker;

use PhpParser\Node;
use PhpParser\Node\Expr\Variable;
use PHPStan\Analyser\Scope;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleErrorBuilder;

/**
 * Level 2 (worker) — flags $_ENV and $_SESSION (and optionally other request
 * superglobals when strict mode is enabled).
 *
 * FrankenPHP resets $_GET/$_POST/$_COOKIE/$_FILES/$_SERVER/$_REQUEST between
 * worker requests, but **$_ENV is not reset**. Native $_SESSION bypasses the
 * framework session lifecycle. Other superglobals are only flagged when
 * `$flagRequestSuperglobals` is true (strict migration audits).
 *
 * @implements Rule<Variable>
 */
final class NoSuperglobalAccessRule implements Rule
{
    private const ALWAYS = [
        '_ENV',
        '_SESSION',
    ];

    private const REQUEST_OPTIONAL = [
        '_GET',
        '_POST',
        '_COOKIE',
        '_FILES',
        '_REQUEST',
        '_SERVER',
    ];

    public function __construct(
        private readonly bool $flagRequestSuperglobals = false,
    ) {
    }

    public function getNodeType(): string
    {
        return Variable::class;
    }

    public function processNode(Node $node, Scope $scope): array
    {
        if (!$node instanceof Variable || !\is_string($node->name)) {
            return [];
        }

        $watched = self::ALWAYS;
        if ($this->flagRequestSuperglobals) {
            $watched = array_merge($watched, self::REQUEST_OPTIONAL);
        }

        if (!\in_array($node->name, $watched, true)) {
            return [];
        }

        if ('_ENV' === $node->name) {
            return [
                RuleErrorBuilder::message(
                    '$_ENV is not reset between FrankenPHP worker requests. Do not read/write request-specific or sensitive data via $_ENV; use the container / config.'
                )
                    ->identifier('frankenphp.worker.noEnvMutation')
                    ->build(),
            ];
        }

        if ('_SESSION' === $node->name) {
            return [
                RuleErrorBuilder::message(
                    'Direct $_SESSION access bypasses framework session reset under FrankenPHP worker mode. Use the framework session API.'
                )
                    ->identifier('frankenphp.worker.noSuperglobalAccess')
                    ->build(),
            ];
        }

        return [
            RuleErrorBuilder::message(
                \sprintf(
                    'Direct access to $%s is flagged in strict mode. Prefer the framework Request API (FrankenPHP does reset most request superglobals; this is a migration hygiene check).',
                    $node->name
                )
            )
                ->identifier('frankenphp.worker.noSuperglobalAccess')
                ->build(),
        ];
    }
}

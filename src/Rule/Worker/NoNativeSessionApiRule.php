<?php

declare(strict_types=1);

namespace NowoTech\PhpStanFrankenPhp\Rule\Worker;

use NowoTech\PhpStanFrankenPhp\Support\NodeHelper;
use PhpParser\Node;
use PhpParser\Node\Expr\FuncCall;
use PHPStan\Analyser\Scope;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleErrorBuilder;

/**
 * Level 2 (worker) — flags native session_* functions outside the framework.
 *
 * Manual session_start()/session_write_close() fight FrankenPHP + framework
 * session reset and can leave session files/locks across requests.
 *
 * @implements Rule<FuncCall>
 */
final class NoNativeSessionApiRule implements Rule
{
    private const FUNCTIONS = [
        'session_start',
        'session_destroy',
        'session_write_close',
        'session_abort',
        'session_reset',
        'session_unset',
        'session_regenerate_id',
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

        $name = $node->name instanceof Node\Name ? $node->name->toString() : 'session_*';

        return [
            RuleErrorBuilder::message(
                \sprintf(
                    '%s() bypasses framework session lifecycle. Under FrankenPHP worker mode use the framework session service so state is reset between requests.',
                    $name
                )
            )
                ->identifier('frankenphp.worker.noNativeSessionApi')
                ->build(),
        ];
    }
}

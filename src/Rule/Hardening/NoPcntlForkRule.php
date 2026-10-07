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
 * Level 3 (hardening) — flags pcntl_fork / pcntl_exec / pcntl_wait* in request code.
 *
 * FrankenPHP runs PHP in threads inside a Go process. Forking or blocking on a
 * child process from a threaded SAPI is unsafe and unsupported for request handlers.
 *
 * @implements Rule<FuncCall>
 */
final class NoPcntlForkRule implements Rule
{
    private const FORK_FUNCTIONS = [
        'pcntl_fork',
        'pcntl_exec',
        'pcntl_rfork',
    ];

    private const WAIT_FUNCTIONS = [
        'pcntl_wait',
        'pcntl_waitpid',
    ];

    public function getNodeType(): string
    {
        return FuncCall::class;
    }

    public function processNode(Node $node, Scope $scope): array
    {
        if (!$node instanceof FuncCall) {
            return [];
        }

        $isWait = NodeHelper::isFunctionNamed($node, self::WAIT_FUNCTIONS);
        $isFork = NodeHelper::isFunctionNamed($node, self::FORK_FUNCTIONS);
        if (!$isWait && !$isFork) {
            return [];
        }

        $name = $node->name instanceof Node\Name ? $node->name->toString() : 'pcntl_*';

        $message = $isWait
            ? \sprintf(
                '%s() blocks the request thread waiting on a child process — unsafe under FrankenPHP (threaded SAPI). Do not pair fork/wait with HTTP workers; use a queue worker or a dedicated CLI/supervisor process.',
                $name
            )
            : \sprintf(
                '%s() is unsafe under FrankenPHP (threaded SAPI). Run isolated work in a separate process/container or a queue worker, not via fork from the request thread.',
                $name
            );

        return [
            RuleErrorBuilder::message($message)
                ->identifier('frankenphp.hardening.noPcntlFork')
                ->build(),
        ];
    }
}

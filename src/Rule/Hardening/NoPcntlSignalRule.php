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
 * Level 3 (hardening) — flags pcntl signal APIs in request code.
 *
 * FrankenPHP uses a threaded SAPI. Installing, masking, waiting on, or
 * dispatching process signals from request threads is unsafe and can interfere
 * with the Go runtime.
 *
 * @implements Rule<FuncCall>
 */
final class NoPcntlSignalRule implements Rule
{
    private const FUNCTIONS = [
        'pcntl_signal',
        'pcntl_async_signals',
        'pcntl_signal_dispatch',
        'pcntl_signal_get_handler',
        'pcntl_sigprocmask',
        'pcntl_sigwaitinfo',
        'pcntl_sigtimedwait',
        'pcntl_alarm',
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

        $name = $node->name instanceof Node\Name ? $node->name->toString() : 'pcntl_*';

        return [
            RuleErrorBuilder::message(
                \sprintf(
                    '%s() is unsafe under FrankenPHP (threaded SAPI). Handle signals outside the request thread (supervisor / dedicated process), not from application request code.',
                    $name
                )
            )
                ->identifier('frankenphp.hardening.noPcntlSignal')
                ->build(),
        ];
    }
}

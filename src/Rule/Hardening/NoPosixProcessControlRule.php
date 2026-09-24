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
 * Level 3 (hardening) — flags posix process-control APIs in request code.
 *
 * Under FrankenPHP the Go/Caddy process owns the OS process. Changing uid/gid
 * or signalling from a request thread is unsafe with a reused worker kernel.
 *
 * @implements Rule<FuncCall>
 */
final class NoPosixProcessControlRule implements Rule
{
    private const FUNCTIONS = [
        'posix_kill',
        'posix_setuid',
        'posix_seteuid',
        'posix_setgid',
        'posix_setegid',
        'posix_setpgid',
        'posix_setsid',
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

        $name = $node->name instanceof Node\Name ? $node->name->toString() : 'posix_*';

        return [
            RuleErrorBuilder::message(
                \sprintf(
                    '%s() mutates OS process identity or signals and is unsafe under FrankenPHP workers (especially with FRANKENPHP_RESET_KERNEL unset/false). Handle process control in the supervisor / a dedicated CLI process.',
                    $name
                )
            )
                ->identifier('frankenphp.hardening.noPosixProcessControl')
                ->build(),
        ];
    }
}

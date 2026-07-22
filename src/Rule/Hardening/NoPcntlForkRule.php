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
 * Level 3 (hardening) — flags pcntl_fork / pcntl_exec in request code.
 *
 * FrankenPHP runs PHP in threads inside a Go process. Forking from a threaded
 * SAPI is unsafe and unsupported for request handlers.
 *
 * @implements Rule<FuncCall>
 */
final class NoPcntlForkRule implements Rule
{
    private const FUNCTIONS = [
        'pcntl_fork',
        'pcntl_exec',
        'pcntl_rfork',
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
                    '%s() is unsafe under FrankenPHP (threaded SAPI). Run isolated work in a separate process/container or a queue worker, not via fork from the request thread.',
                    $name
                )
            )
                ->identifier('frankenphp.hardening.noPcntlFork')
                ->build(),
        ];
    }
}

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
 * Level 2 (worker) — flags register_shutdown_function().
 *
 * Under FrankenPHP worker mode, shutdown functions run when the worker script
 * ends (or on fatal errors), not after each HTTP request. FPM-era “run after
 * response” patterns silently break.
 *
 * @implements Rule<FuncCall>
 */
final class NoRegisterShutdownFunctionRule implements Rule
{
    public function getNodeType(): string
    {
        return FuncCall::class;
    }

    public function processNode(Node $node, Scope $scope): array
    {
        if (!$node instanceof FuncCall || !NodeHelper::isFunctionNamedExactly($node, 'register_shutdown_function')) {
            return [];
        }

        return [
            RuleErrorBuilder::message(
                'register_shutdown_function() does not run after each FrankenPHP worker request; it runs when the worker script ends. Use framework terminate events, Messenger, or a queue for post-response work.'
            )
                ->identifier('frankenphp.worker.noRegisterShutdownFunction')
                ->build(),
        ];
    }
}

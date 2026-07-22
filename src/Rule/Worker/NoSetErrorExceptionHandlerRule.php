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
 * Level 2 (worker) — flags set_error_handler() / set_exception_handler().
 *
 * Handlers registered mid-request stick on the worker process and are easy to
 * leak across requests. Prefer framework exception listeners / ErrorHandler.
 *
 * @implements Rule<FuncCall>
 */
final class NoSetErrorExceptionHandlerRule implements Rule
{
    private const FUNCTIONS = [
        'set_error_handler',
        'set_exception_handler',
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

        $name = $node->name instanceof Node\Name ? $node->name->toString() : 'set_*_handler';

        return [
            RuleErrorBuilder::message(
                \sprintf(
                    '%s() registers a process-wide handler that persists on FrankenPHP workers. Prefer framework error/exception listeners and restore previous handlers if you must use natives temporarily.',
                    $name
                )
            )
                ->identifier('frankenphp.worker.noSetErrorExceptionHandler')
                ->build(),
        ];
    }
}

<?php

declare(strict_types=1);

namespace NowoTech\PhpStanFrankenPhp\Rule\Worker;

use NowoTech\PhpStanFrankenPhp\Support\NodeHelper;
use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\FuncCall;
use PHPStan\Analyser\Scope;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleErrorBuilder;

/**
 * Level 2 (worker) — flags error_reporting() when used to change the level.
 *
 * Calls without an argument only read the current level and are allowed.
 *
 * @implements Rule<FuncCall>
 */
final class NoErrorReportingMutationRule implements Rule
{
    public function getNodeType(): string
    {
        return FuncCall::class;
    }

    public function processNode(Node $node, Scope $scope): array
    {
        if (!$node instanceof FuncCall || !NodeHelper::isFunctionNamedExactly($node, 'error_reporting')) {
            return [];
        }

        if (!NodeHelper::firstArgExpr($node) instanceof Expr) {
            return [];
        }

        return [
            RuleErrorBuilder::message(
                'error_reporting() with a level argument changes process-wide error reporting that persists on FrankenPHP workers. Configure error_reporting in php.ini / the FrankenPHP image instead.'
            )
                ->identifier('frankenphp.worker.noErrorReportingMutation')
                ->build(),
        ];
    }
}

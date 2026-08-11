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
 * Level 2 (worker) — flags umask() when used to change the process umask.
 *
 * Calls without an argument only read the current umask and are allowed.
 *
 * @implements Rule<FuncCall>
 */
final class NoUmaskRule implements Rule
{
    public function getNodeType(): string
    {
        return FuncCall::class;
    }

    public function processNode(Node $node, Scope $scope): array
    {
        if (!$node instanceof FuncCall || !NodeHelper::isFunctionNamedExactly($node, 'umask')) {
            return [];
        }

        if (!NodeHelper::firstArgExpr($node) instanceof Expr) {
            return [];
        }

        return [
            RuleErrorBuilder::message(
                'umask() with a mask argument changes the process file-creation mask and persists on FrankenPHP workers. Set umask in the process supervisor / container entrypoint instead of per request.'
            )
                ->identifier('frankenphp.worker.noUmask')
                ->build(),
        ];
    }
}

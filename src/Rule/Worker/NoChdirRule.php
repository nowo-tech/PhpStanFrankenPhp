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
 * Level 2 (worker) — flags chdir(), which changes the process working directory.
 *
 * The CWD is process-wide and survives across FrankenPHP worker requests, so a
 * later request can resolve relative paths against another request's directory.
 *
 * @implements Rule<FuncCall>
 */
final class NoChdirRule implements Rule
{
    public function getNodeType(): string
    {
        return FuncCall::class;
    }

    public function processNode(Node $node, Scope $scope): array
    {
        if (!$node instanceof FuncCall || !NodeHelper::isFunctionNamedExactly($node, 'chdir')) {
            return [];
        }

        return [
            RuleErrorBuilder::message(
                'chdir() changes the process working directory and persists on FrankenPHP workers, so later requests may resolve relative paths incorrectly. Use absolute paths or inject a base path from config instead.'
            )
                ->identifier('frankenphp.worker.noChdir')
                ->build(),
        ];
    }
}

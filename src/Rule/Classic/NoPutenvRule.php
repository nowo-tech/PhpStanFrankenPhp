<?php

declare(strict_types=1);

namespace NowoTech\PhpStanFrankenPhp\Rule\Classic;

use NowoTech\PhpStanFrankenPhp\Support\NodeHelper;
use PhpParser\Node;
use PhpParser\Node\Expr\FuncCall;
use PHPStan\Analyser\Scope;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleErrorBuilder;

/**
 * Level 1 (classic) — flags putenv(), which mutates process environment.
 *
 * Environment changes survive the request under FrankenPHP and bleed into later
 * requests (especially worker mode). Prefer dotenv / framework config injection.
 *
 * @implements Rule<FuncCall>
 */
final class NoPutenvRule implements Rule
{
    public function getNodeType(): string
    {
        return FuncCall::class;
    }

    public function processNode(Node $node, Scope $scope): array
    {
        if (!$node instanceof FuncCall || !NodeHelper::isFunctionNamedExactly($node, 'putenv')) {
            return [];
        }

        return [
            RuleErrorBuilder::message(
                'putenv() mutates the process environment and persists across requests under FrankenPHP. Configure environment via .env / container parameters instead.'
            )
                ->identifier('frankenphp.classic.noPutenv')
                ->build(),
        ];
    }
}

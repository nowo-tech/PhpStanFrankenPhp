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
 * Level 2 (worker) — flags date_default_timezone_set().
 *
 * The default timezone is process-wide. Changing it mid-request alters date/time
 * behaviour for every later request on the same FrankenPHP worker thread.
 *
 * @implements Rule<FuncCall>
 */
final class NoDateDefaultTimezoneSetRule implements Rule
{
    public function getNodeType(): string
    {
        return FuncCall::class;
    }

    public function processNode(Node $node, Scope $scope): array
    {
        if (!$node instanceof FuncCall || !NodeHelper::isFunctionNamedExactly($node, 'date_default_timezone_set')) {
            return [];
        }

        return [
            RuleErrorBuilder::message(
                'date_default_timezone_set() changes the process default timezone and persists on FrankenPHP workers. Configure date.timezone in php.ini / the FrankenPHP image, or pass explicit timezones to DateTime APIs.'
            )
                ->identifier('frankenphp.worker.noDateDefaultTimezoneSet')
                ->build(),
        ];
    }
}

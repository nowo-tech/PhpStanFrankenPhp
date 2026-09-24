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
 * Level 2 (worker) — flags mb_* setters that stick on the worker.
 *
 * Calls without an argument, or with an explicit `null` argument (PHP 8 getters),
 * only read the current value and are allowed.
 *
 * @implements Rule<FuncCall>
 */
final class NoMbEncodingMutationRule implements Rule
{
    private const FUNCTIONS = [
        'mb_internal_encoding',
        'mb_regex_encoding',
        'mb_http_output',
        'mb_language',
        'mb_detect_order',
        'mb_substitute_character',
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

        $first = NodeHelper::firstArgExpr($node);
        if (!$first instanceof Expr || NodeHelper::isNullLiteral($first)) {
            return [];
        }

        $name = $node->name instanceof Node\Name ? $node->name->toString() : 'mb_*';

        return [
            RuleErrorBuilder::message(
                \sprintf(
                    '%s() with an argument mutates process-wide mbstring state that persists on FrankenPHP workers. Configure mbstring in php.ini / the FrankenPHP image, or pass encodings/language explicitly to mb_* functions.',
                    $name
                )
            )
                ->identifier('frankenphp.worker.noMbEncodingMutation')
                ->build(),
        ];
    }
}

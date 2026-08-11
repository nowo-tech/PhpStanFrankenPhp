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
 * Level 2 (worker) — flags setlocale() when used to change process locale state.
 *
 * Locale is process-wide. A request that mutates it leaks formatting, collation,
 * and message behaviour into every later request on the same worker.
 *
 * Calls with locale `0` / `"0"` only query the current setting and are allowed.
 *
 * @implements Rule<FuncCall>
 */
final class NoSetLocaleRule implements Rule
{
    public function getNodeType(): string
    {
        return FuncCall::class;
    }

    public function processNode(Node $node, Scope $scope): array
    {
        if (!$node instanceof FuncCall || !NodeHelper::isFunctionNamedExactly($node, 'setlocale')) {
            return [];
        }

        $localeExpr = NodeHelper::argExpr($node, 1);
        if ($localeExpr instanceof Expr && $this->isLocaleQueryLiteral($localeExpr)) {
            return [];
        }

        return [
            RuleErrorBuilder::message(
                'setlocale() with a locale argument mutates process-wide locale state that persists on FrankenPHP workers. Prefer framework request locale (e.g. Symfony Translator / Request::setLocale) or configure locale in php.ini / the image. Queries via setlocale($category, 0) stay allowed.'
            )
                ->identifier('frankenphp.worker.noSetLocale')
                ->build(),
        ];
    }

    private function isLocaleQueryLiteral(Expr $expr): bool
    {
        if ($expr instanceof Node\Scalar\LNumber) {
            return 0 === $expr->value;
        }

        return $expr instanceof Node\Scalar\String_ && '0' === $expr->value;
    }
}

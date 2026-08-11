<?php

declare(strict_types=1);

namespace NowoTech\PhpStanFrankenPhp\Rule\Worker;

use NowoTech\PhpStanFrankenPhp\Support\NodeHelper;
use PhpParser\Node;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name;
use PHPStan\Analyser\Scope;
use PHPStan\Rules\IdentifierRuleError;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleErrorBuilder;

/**
 * Level 2 (worker) — flags intl default-locale mutation.
 *
 * `locale_set_default()` / `Locale::setDefault()` change the process-wide ICU
 * default locale, which sticks on FrankenPHP workers across requests.
 *
 * @implements Rule<Node>
 */
final class NoLocaleSetDefaultRule implements Rule
{
    public function getNodeType(): string
    {
        return Node::class;
    }

    public function processNode(Node $node, Scope $scope): array
    {
        if ($node instanceof FuncCall && NodeHelper::isFunctionNamedExactly($node, 'locale_set_default')) {
            return [$this->error('locale_set_default()')];
        }

        if ($node instanceof StaticCall
            && $node->name instanceof Identifier
            && 0 === strcasecmp($node->name->toString(), 'setDefault')
            && $node->class instanceof Name
            && 'Locale' === ltrim($scope->resolveName($node->class), '\\')
        ) {
            return [$this->error('Locale::setDefault()')];
        }

        return [];
    }

    private function error(string $call): IdentifierRuleError
    {
        return RuleErrorBuilder::message(
            \sprintf(
                '%s mutates the process-wide ICU default locale that persists on FrankenPHP workers. Prefer request/framework locale (e.g. Symfony Request::setLocale / Translator) or configure intl defaults in php.ini / the image.',
                $call
            )
        )
            ->identifier('frankenphp.worker.noLocaleSetDefault')
            ->build();
    }
}

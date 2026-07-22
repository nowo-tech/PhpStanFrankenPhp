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
 * Level 1 (classic) — flags fastcgi_finish_request(), which is PHP-FPM/CGI specific.
 *
 * FrankenPHP does not provide FastCGI. Calls are no-ops or fatal depending on
 * the SAPI and leave "finish early then keep working" patterns broken.
 *
 * @implements Rule<FuncCall>
 */
final class NoFastCgiFinishRequestRule implements Rule
{
    public function getNodeType(): string
    {
        return FuncCall::class;
    }

    public function processNode(Node $node, Scope $scope): array
    {
        if (!$node instanceof FuncCall || !NodeHelper::isFunctionNamedExactly($node, 'fastcgi_finish_request')) {
            return [];
        }

        return [
            RuleErrorBuilder::message(
                'fastcgi_finish_request() is PHP-FPM/CGI specific and is not available under FrankenPHP. Flush the framework response and use async/messaging for post-response work instead.'
            )
                ->identifier('frankenphp.classic.noFastCgiFinishRequest')
                ->build(),
        ];
    }
}

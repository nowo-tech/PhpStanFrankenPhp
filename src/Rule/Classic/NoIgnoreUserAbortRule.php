<?php

declare(strict_types=1);

namespace NowoTech\PhpStanFrankenPhp\Rule\Classic;

use NowoTech\PhpStanFrankenPhp\Support\NodeHelper;
use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\FuncCall;
use PHPStan\Analyser\Scope;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleErrorBuilder;

/**
 * Level 1 (classic) — flags ignore_user_abort(true)-style usage.
 *
 * Continuing after the client disconnects pins a FrankenPHP thread/worker and
 * can exhaust the pool. Prefer explicit queue jobs for post-disconnect work.
 *
 * @implements Rule<FuncCall>
 */
final class NoIgnoreUserAbortRule implements Rule
{
    public function getNodeType(): string
    {
        return FuncCall::class;
    }

    public function processNode(Node $node, Scope $scope): array
    {
        if (!$node instanceof FuncCall || !NodeHelper::isFunctionNamedExactly($node, 'ignore_user_abort')) {
            return [];
        }

        $arg = NodeHelper::firstArgExpr($node);
        // No argument = query current setting; only flag enabling calls.
        if (!$arg instanceof Expr) {
            return [];
        }

        $enables = false;
        if ($arg instanceof Expr\ConstFetch) {
            $name = strtolower($arg->name->toString());
            $enables = \in_array($name, ['true', '1'], true);
        } elseif ($arg instanceof Node\Scalar\LNumber) {
            $enables = 1 === $arg->value;
        } elseif ($arg instanceof Node\Scalar\String_) {
            $enables = \in_array(strtolower($arg->value), ['1', 'true'], true);
        }

        if (!$enables) {
            return [];
        }

        return [
            RuleErrorBuilder::message(
                'ignore_user_abort(true) keeps work running after disconnect and can pin a FrankenPHP thread. Use a queue for background work instead.'
            )
                ->identifier('frankenphp.classic.noIgnoreUserAbort')
                ->build(),
        ];
    }
}

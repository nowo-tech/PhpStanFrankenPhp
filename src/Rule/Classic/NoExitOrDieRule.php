<?php

declare(strict_types=1);

namespace NowoTech\PhpStanFrankenPhp\Rule\Classic;

use PhpParser\Node;
use PhpParser\Node\Expr\Exit_;
use PHPStan\Analyser\Scope;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleErrorBuilder;

/**
 * Level 1 (classic) — flags exit()/die() which terminate the PHP process.
 *
 * Under FrankenPHP (classic or worker) a single exit can tear down the
 * embedded PHP runtime / worker thread instead of ending only one FPM request.
 *
 * @implements Rule<Exit_>
 */
final class NoExitOrDieRule implements Rule
{
    public function getNodeType(): string
    {
        return Exit_::class;
    }

    public function processNode(Node $node, Scope $scope): array
    {
        return [
            RuleErrorBuilder::message(
                'Do not use exit/die under FrankenPHP: it terminates the PHP process (or worker thread), not a single FPM request. Throw an exception or return an HTTP response instead.'
            )
                ->identifier('frankenphp.classic.noExitOrDie')
                ->build(),
        ];
    }
}

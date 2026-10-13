<?php

declare(strict_types=1);

namespace NowoTech\PhpStanFrankenPhp\Rule\Worker;

use NowoTech\PhpStanFrankenPhp\Support\NodeHelper;
use PhpParser\Node;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Scalar\String_;
use PHPStan\Analyser\Scope;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleErrorBuilder;

/**
 * Level 2 (worker) — flags ini_set() (and its alias ini_alter()) for settings that stick on the worker.
 *
 * Many ini values are process-wide. Changing them mid-request alters behaviour
 * for every later request on the same FrankenPHP worker thread.
 *
 * @implements Rule<FuncCall>
 */
final class NoPersistentIniSetRule implements Rule
{
    private const PERSISTENT_KEYS = [
        'memory_limit',
        'max_execution_time',
        'date.timezone',
        'error_reporting',
        'display_errors',
        'default_socket_timeout',
        'default_charset',
        'auto_detect_line_endings',
        'precision',
        'serialize_precision',
        'zend.assertions',
        'opcache.enable',
        'session.save_path',
        'session.name',
        'session.gc_maxlifetime',
        'mbstring.internal_encoding',
        'mbstring.http_output',
        'mbstring.language',
        'mbstring.detect_order',
        'mbstring.substitute_character',
        'intl.default_locale',
    ];

    public function getNodeType(): string
    {
        return FuncCall::class;
    }

    public function processNode(Node $node, Scope $scope): array
    {
        if (!$node instanceof FuncCall || !NodeHelper::isFunctionNamed($node, ['ini_set', 'ini_alter'])) {
            return [];
        }

        $function = NodeHelper::isFunctionNamedExactly($node, 'ini_alter') ? 'ini_alter' : 'ini_set';

        $keyExpr = NodeHelper::firstArgExpr($node);
        if (!$keyExpr instanceof String_) {
            return [
                RuleErrorBuilder::message(
                    \sprintf(
                        '%s() may change process-wide settings that persist on FrankenPHP workers. Prefer php.ini / FrankenPHP image config, or limit changes to request-safe keys with an explicit reset.',
                        $function
                    )
                )
                    ->identifier('frankenphp.worker.noPersistentIniSet')
                    ->build(),
            ];
        }

        $key = strtolower($keyExpr->value);
        if (!\in_array($key, self::PERSISTENT_KEYS, true)) {
            return [];
        }

        return [
            RuleErrorBuilder::message(
                \sprintf(
                    '%s(\'%s\', …) changes a process-wide setting that persists on FrankenPHP workers and affects later requests. Configure it in php.ini / the FrankenPHP image instead.',
                    $function,
                    $keyExpr->value
                )
            )
                ->identifier('frankenphp.worker.noPersistentIniSet')
                ->build(),
        ];
    }
}

<?php

declare(strict_types=1);

namespace NowoTech\PhpStanFrankenPhp\Rule\Classic;

use NowoTech\PhpStanFrankenPhp\Support\NodeHelper;
use PhpParser\Node;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Identifier;
use PHPStan\Analyser\Scope;
use PHPStan\Rules\IdentifierRuleError;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleErrorBuilder;

/**
 * Level 1 (classic) — flags unbounded process / curl timeouts.
 *
 * A hung subprocess or HTTP call occupies a FrankenPHP thread indefinitely
 * (REQ-RUNTIME-001). Detects setTimeout(null|0), curl CURLOPT_* timeout 0,
 * and proc_open without an obvious timeout wrapper (advisory on proc_open).
 *
 * @implements Rule<Node>
 */
final class NoUnlimitedIoTimeoutRule implements Rule
{
    public function getNodeType(): string
    {
        return Node::class;
    }

    public function processNode(Node $node, Scope $scope): array
    {
        if ($node instanceof MethodCall) {
            return $this->processMethodCall($node);
        }

        if ($node instanceof FuncCall) {
            return $this->processFuncCall($node);
        }

        return [];
    }

    /**
     * @return list<IdentifierRuleError>
     */
    private function processMethodCall(MethodCall $node): array
    {
        if (!$node->name instanceof Identifier) {
            return [];
        }

        $method = $node->name->toString();
        if ('setTimeout' !== $method && 'setIdleTimeout' !== $method) {
            return [];
        }

        if ([] === $node->args || !isset($node->args[0]) || !$node->args[0] instanceof Node\Arg) {
            return [];
        }

        if (!NodeHelper::isZeroLikeLiteral($node->args[0]->value)) {
            return [];
        }

        return [
            RuleErrorBuilder::message(
                \sprintf(
                    'Unlimited %s() leaves a FrankenPHP thread blocked if the child never ends. Set a finite timeout (seconds) and fail controlled.',
                    $method
                )
            )
                ->identifier('frankenphp.classic.noUnlimitedIoTimeout')
                ->build(),
        ];
    }

    /**
     * @return list<IdentifierRuleError>
     */
    private function processFuncCall(FuncCall $node): array
    {
        if (NodeHelper::isFunctionNamedExactly($node, 'proc_open')) {
            return [
                RuleErrorBuilder::message(
                    'proc_open() has no built-in timeout. Under FrankenPHP prefer Symfony Process with setTimeout()/setIdleTimeout(), or wrap with an explicit deadline and cleanup.'
                )
                    ->identifier('frankenphp.classic.noUnlimitedIoTimeout')
                    ->build(),
            ];
        }

        if (!NodeHelper::isFunctionNamed($node, ['curl_setopt', 'curl_setopt_array'])) {
            return [];
        }

        if (NodeHelper::isFunctionNamedExactly($node, 'curl_setopt')) {
            return $this->checkCurlSetopt($node);
        }

        return $this->checkCurlSetoptArray($node);
    }

    /**
     * @return list<IdentifierRuleError>
     */
    private function checkCurlSetopt(FuncCall $node): array
    {
        $option = NodeHelper::argExpr($node, 1);
        $value = NodeHelper::argExpr($node, 2);
        if (null === $option || null === $value) {
            return [];
        }

        if (!$this->isCurlTimeoutOption($option) || !NodeHelper::isZeroLikeLiteral($value)) {
            return [];
        }

        return [
            RuleErrorBuilder::message(
                'curl timeout option set to 0/null disables the deadline and can pin a FrankenPHP thread. Use a positive CURLOPT_TIMEOUT / CURLOPT_TIMEOUT_MS.'
            )
                ->identifier('frankenphp.classic.noUnlimitedIoTimeout')
                ->build(),
        ];
    }

    /**
     * @return list<IdentifierRuleError>
     */
    private function checkCurlSetoptArray(FuncCall $node): array
    {
        $options = NodeHelper::argExpr($node, 1);
        if (!$options instanceof Node\Expr\Array_) {
            return [];
        }

        foreach ($options->items as $item) {
            if (null === $item || null === $item->key) {
                continue;
            }
            if ($this->isCurlTimeoutOption($item->key) && NodeHelper::isZeroLikeLiteral($item->value)) {
                return [
                    RuleErrorBuilder::message(
                        'curl timeout option set to 0/null disables the deadline and can pin a FrankenPHP thread. Use a positive CURLOPT_TIMEOUT / CURLOPT_TIMEOUT_MS.'
                    )
                        ->identifier('frankenphp.classic.noUnlimitedIoTimeout')
                        ->build(),
                ];
            }
        }

        return [];
    }

    private function isCurlTimeoutOption(Node\Expr $expr): bool
    {
        if (!$expr instanceof Node\Expr\ConstFetch) {
            return false;
        }

        $name = strtoupper($expr->name->toString());

        return \in_array($name, [
            'CURLOPT_TIMEOUT',
            'CURLOPT_TIMEOUT_MS',
            'CURLOPT_CONNECTTIMEOUT',
            'CURLOPT_CONNECTTIMEOUT_MS',
        ], true);
    }
}

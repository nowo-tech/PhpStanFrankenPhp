<?php

declare(strict_types=1);

namespace NowoTech\PhpStanFrankenPhp\Rule\Worker;

use NowoTech\PhpStanFrankenPhp\Support\AstFinder;
use NowoTech\PhpStanFrankenPhp\Support\DoctrineExprResolver;
use PhpParser\Node;
use PhpParser\Node\FunctionLike;
use PhpParser\Node\Stmt\ElseIf_;
use PhpParser\Node\Stmt\Finally_;
use PhpParser\Node\Stmt\If_;
use PhpParser\Node\Stmt\TryCatch;
use PHPStan\Analyser\Scope;
use PHPStan\Node\InClassNode;
use PHPStan\Rules\IdentifierRuleError;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleErrorBuilder;

/**
 * Level 2 (worker, opt-in) — flags `rollBack()` in a `catch` / `finally` when the transaction
 * it rolls back can be nested.
 *
 * In DBAL 4 a nested `rollBack()` runs `ROLLBACK TO SAVEPOINT` before it lowers the nesting
 * level. After a MySQL deadlock (1213) the server has rolled back the whole transaction and
 * dropped every savepoint, so that rollback fails with 1305 and the connection stays nested
 * for every later request of the worker; the caller sees 1305 instead of the deadlock.
 *
 * Heuristic, per `try` statement whose catch / finally calls `rollBack()` / `rollback()` on a
 * connection or entity manager (or an untyped receiver):
 * - `beginTransaction()` runs in the same function before the `try`, or
 * - the `try` block calls `beginTransaction()`, `wrapInTransaction()` / `transactional()` or an
 *   ORM `flush()` (the ORM opens its own, nested, transaction).
 *
 * Not reported: a rollback inside its own `try` whose catch / finally calls `close()`, a
 * rollback after a `close()` in the same handler, and a rollback under an `if` that checks
 * `getTransactionNestingLevel()`. A rollback under `isTransactionActive()` is still reported:
 * the transaction is still active at the nesting level the failed rollback left behind.
 *
 * Enable via `frankenphp.flagRollbackAfterNestedBegin: true` or `ruleset-worker-load.neon`.
 *
 * @implements Rule<InClassNode>
 */
final class NoRollbackAfterNestedBeginRule implements Rule
{
    private const MESSAGE = '%s() in a catch/finally after %s can run on a nested transaction: after a deadlock MySQL drops every savepoint, the rollback fails with 1305 and the connection stays nested for every later request of the worker.%s Close the connection on failure (or roll back only at nesting level 1), or run the work through a TransactionRunnerInterface.';

    private const GUARD_NOTE = ' The isTransactionActive() guard does not help: the transaction is still active at the level the failed rollback leaves.';

    public function __construct(
        private readonly bool $enabled = false,
    ) {
    }

    public function getNodeType(): string
    {
        return InClassNode::class;
    }

    public function processNode(Node $node, Scope $scope): array
    {
        if (!$this->enabled) {
            return [];
        }

        $errors = [];
        $reported = [];
        foreach ($node->getOriginalNode()->getMethods() as $method) {
            $resolver = DoctrineExprResolver::forFunction($scope, $node->getClassReflection(), $method);
            foreach (AstFinder::findAll($method, static fn (Node $n): bool => $n instanceof FunctionLike, true) as $function) {
                if ($function instanceof FunctionLike) {
                    $this->processFunction($function->getStmts() ?? [], $resolver, $reported, $errors);
                }
            }
        }

        return $errors;
    }

    /**
     * @param array<Node\Stmt>          $body
     * @param array<int, true>          $reported
     * @param list<IdentifierRuleError> $errors
     */
    private function processFunction(array $body, DoctrineExprResolver $resolver, array &$reported, array &$errors): void
    {
        $begins = AstFinder::findAll($body, static fn (Node $n): bool => $resolver->isTransactionCall($n, ['begintransaction']));
        foreach (AstFinder::findAll($body, static fn (Node $n): bool => $n instanceof TryCatch) as $try) {
            /** @var TryCatch $try */
            $reason = $this->reason($try, $begins, $resolver);
            if (null === $reason) {
                continue;
            }
            $handlers = [];
            foreach ($try->catches as $catch) {
                $handlers[] = $catch->stmts;
            }
            if (null !== $try->finally) {
                $handlers[] = $try->finally->stmts;
            }
            foreach ($handlers as $stmts) {
                $closes = AstFinder::calls($stmts, ['close']);
                $this->walk($stmts, false, false, $closes, $reason, $resolver, $reported, $errors);
            }
        }
    }

    /**
     * @param list<Node> $begins
     */
    private function reason(TryCatch $try, array $begins, DoctrineExprResolver $resolver): ?string
    {
        foreach ($begins as $begin) {
            if ($begin->getEndFilePos() < $try->getStartFilePos()) {
                return 'beginTransaction()';
            }
        }
        $inside = AstFinder::findAll($try->stmts, static fn (Node $n): bool => null !== $resolver->flushKind($n)
            || $resolver->isTransactionCall($n, ['begintransaction', 'wrapintransaction', 'transactional']));
        if ([] === $inside) {
            return null;
        }
        $name = AstFinder::callName($inside[0]);

        return 'flush' === $name ? 'an ORM flush() in the try block' : AstFinder::terminalName($inside[0]).'() in the try block';
    }

    /**
     * @param array<mixed>              $nodes
     * @param list<Node>                $closes   close() calls of the handler
     * @param array<int, true>          $reported
     * @param list<IdentifierRuleError> $errors
     */
    private function walk(array $nodes, bool $safe, bool $guarded, array $closes, string $reason, DoctrineExprResolver $resolver, array &$reported, array &$errors): void
    {
        foreach ($nodes as $node) {
            if (\is_array($node)) {
                $this->walk($node, $safe, $guarded, $closes, $reason, $resolver, $reported, $errors);
                continue;
            }
            if (!$node instanceof Node || $node instanceof FunctionLike || $node instanceof Node\Stmt\ClassLike) {
                continue;
            }
            if ($node instanceof TryCatch) {
                $closing = [];
                foreach ($node->catches as $catch) {
                    $closing[] = $catch->stmts;
                }
                if ($node->finally instanceof Finally_) {
                    $closing[] = $node->finally->stmts;
                }
                $this->walk($node->stmts, $safe || [] !== AstFinder::calls($closing, ['close']), $guarded, $closes, $reason, $resolver, $reported, $errors);
                $this->walk($closing, $safe, $guarded, $closes, $reason, $resolver, $reported, $errors);
                continue;
            }
            if ($node instanceof If_ || $node instanceof ElseIf_) {
                $nesting = [] !== AstFinder::calls($node->cond, ['gettransactionnestinglevel']);
                $active = [] !== AstFinder::calls($node->cond, ['istransactionactive']);
                $this->walk($node instanceof If_ ? [$node->stmts, $node->elseifs, $node->else] : $node->stmts, $safe || $nesting, $guarded || $active, $closes, $reason, $resolver, $reported, $errors);
                continue;
            }
            if ($resolver->isTransactionCall($node, ['rollback'])
                && !$safe && !$this->closedBefore($node, $closes) && !isset($reported[spl_object_id($node)])
            ) {
                $reported[spl_object_id($node)] = true;
                $errors[] = RuleErrorBuilder::message(\sprintf(self::MESSAGE, AstFinder::terminalName($node), $reason, $guarded ? self::GUARD_NOTE : ''))
                    ->identifier('frankenphp.worker.noRollbackAfterNestedBegin')
                    ->line($node->getStartLine())
                    ->build();
            }
            $children = [];
            foreach ($node->getSubNodeNames() as $name) {
                $children[] = $node->{$name};
            }
            $this->walk($children, $safe, $guarded, $closes, $reason, $resolver, $reported, $errors);
        }
    }

    /**
     * @param list<Node> $closes
     */
    private function closedBefore(Node $rollback, array $closes): bool
    {
        foreach ($closes as $close) {
            if ($close->getEndFilePos() < $rollback->getStartFilePos()) {
                return true;
            }
        }

        return false;
    }
}

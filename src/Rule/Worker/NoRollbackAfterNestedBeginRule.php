<?php

declare(strict_types=1);

namespace NowoTech\PhpStanFrankenPhp\Rule\Worker;

use NowoTech\PhpStanFrankenPhp\Support\AstFinder;
use NowoTech\PhpStanFrankenPhp\Support\DoctrineExprResolver;
use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\BinaryOp;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\NullsafeMethodCall;
use PhpParser\Node\FunctionLike;
use PhpParser\Node\Scalar\Int_;
use PhpParser\Node\Stmt\ElseIf_;
use PhpParser\Node\Stmt\Finally_;
use PhpParser\Node\Stmt\If_;
use PhpParser\Node\Stmt\TryCatch;
use PHPStan\Analyser\Scope;
use PHPStan\Node\InClassMethodNode;
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
 * Not reported: a rollback inside its own `try` whose catch / finally calls `close()` on the
 * connection, a rollback after such a `close()` in the same handler, and a rollback under an
 * `if` that pins `getTransactionNestingLevel()` to 1 (`=== 1`, `== 1`, `<= 1`, `< 2`). A rollback
 * under `isTransactionActive()` or another nesting-level test (`> 0`) is still reported: the
 * transaction is still active at the nesting level the failed rollback left behind.
 *
 * Runs per method (`InClassMethodNode`), so trait methods are analysed in each using class and
 * anonymous classes are analysed once, with their own `$this`.
 *
 * Enable via `frankenphp.flagRollbackAfterNestedBegin: true` or `ruleset-worker-load.neon`.
 *
 * @implements Rule<InClassMethodNode>
 */
final class NoRollbackAfterNestedBeginRule implements Rule
{
    private const MESSAGE = '%s() in a catch/finally after %s can run on a nested transaction: after a deadlock (MySQL/MariaDB, DBAL 4 savepoints) the server drops every savepoint, the rollback fails with 1305 and the connection stays nested for every later request of the worker.%s Roll back to the level the transaction started at and close the connection only if that rollback throws (try { $connection->rollBack(); } catch (\Throwable) { $connection->close(); }), or run the work through TransactionRunnerInterface::run() (nowo-tech/transaction-contract).';

    private const GUARD_NOTE = ' The %s guard does not help: the transaction is still active at the level the failed rollback leaves.';

    public function __construct(
        private readonly bool $enabled = false,
    ) {
    }

    public function getNodeType(): string
    {
        return InClassMethodNode::class;
    }

    public function processNode(Node $node, Scope $scope): array
    {
        if (!$this->enabled) {
            return [];
        }

        $errors = [];
        $reported = [];
        $method = $node->getOriginalNode();
        $resolver = DoctrineExprResolver::forFunction($scope, $node->getClassReflection(), $method);
        foreach (AstFinder::findAll($method, static fn (Node $n): bool => $n instanceof FunctionLike, true) as $function) {
            if ($function instanceof FunctionLike) {
                $this->processFunction($function->getStmts() ?? [], $resolver, $reported, $errors);
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
                $closes = $this->connectionCloses($stmts, $resolver);
                $this->walk($stmts, false, null, $closes, $reason, $resolver, $reported, $errors);
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
    private function walk(array $nodes, bool $safe, ?string $guard, array $closes, string $reason, DoctrineExprResolver $resolver, array &$reported, array &$errors): void
    {
        foreach ($nodes as $node) {
            if (\is_array($node)) {
                $this->walk($node, $safe, $guard, $closes, $reason, $resolver, $reported, $errors);
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
                $this->walk($node->stmts, $safe || [] !== $this->connectionCloses($closing, $resolver), $guard, $closes, $reason, $resolver, $reported, $errors);
                $this->walk($closing, $safe, $guard, $closes, $reason, $resolver, $reported, $errors);
                continue;
            }
            if ($node instanceof If_ || $node instanceof ElseIf_) {
                $this->walk($node instanceof If_ ? [$node->stmts, $node->elseifs, $node->else] : $node->stmts, $safe || $this->pinsLevelOne($node->cond), $guard ?? $this->guardOf($node->cond), $closes, $reason, $resolver, $reported, $errors);
                continue;
            }
            if ($resolver->isTransactionCall($node, ['rollback'])
                && !$safe && !$this->closedBefore($node, $closes) && !isset($reported[spl_object_id($node)])
            ) {
                $reported[spl_object_id($node)] = true;
                $errors[] = RuleErrorBuilder::message(\sprintf(self::MESSAGE, AstFinder::terminalName($node), $reason, null === $guard ? '' : \sprintf(self::GUARD_NOTE, $guard)))
                    ->identifier('frankenphp.worker.noRollbackAfterNestedBegin')
                    ->line($node->getStartLine())
                    ->build();
            }
            $children = [];
            foreach ($node->getSubNodeNames() as $name) {
                $children[] = $node->{$name};
            }
            $this->walk($children, $safe, $guard, $closes, $reason, $resolver, $reported, $errors);
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

    /**
     * `close()` calls on a DBAL connection (EntityManager::close() or a file handle does not reset the nesting level).
     *
     * @param array<mixed> $nodes
     *
     * @return list<Node>
     */
    private function connectionCloses(array $nodes, DoctrineExprResolver $resolver): array
    {
        return array_values(array_filter(
            AstFinder::calls($nodes, ['close']),
            static fn (Node $call): bool => ($call instanceof MethodCall || $call instanceof NullsafeMethodCall) && $resolver->isConnection($call->var),
        ));
    }

    /**
     * Whether the condition compares getTransactionNestingLevel() so that only level 1 passes.
     */
    private function pinsLevelOne(Expr $cond): bool
    {
        foreach (AstFinder::findAll($cond, static fn (Node $n): bool => $n instanceof BinaryOp) as $op) {
            /** @var BinaryOp $op */
            $levelLeft = 'gettransactionnestinglevel' === AstFinder::callName($op->left);
            $levelRight = 'gettransactionnestinglevel' === AstFinder::callName($op->right);
            $number = $levelLeft ? $op->right : ($levelRight ? $op->left : null);
            if (!$number instanceof Int_) {
                continue;
            }
            $pinned = match (true) {
                $op instanceof BinaryOp\Identical, $op instanceof BinaryOp\Equal => 1 === $number->value,
                $op instanceof BinaryOp\SmallerOrEqual => $levelLeft && 1 === $number->value,
                $op instanceof BinaryOp\Smaller => $levelLeft && 2 === $number->value,
                $op instanceof BinaryOp\GreaterOrEqual => $levelRight && 1 === $number->value,
                $op instanceof BinaryOp\Greater => $levelRight && 2 === $number->value,
                default => false,
            };
            if ($pinned) {
                return true;
            }
        }

        return false;
    }

    /**
     * The transaction-state test an `if` relies on, when it does not pin level 1.
     */
    private function guardOf(Expr $cond): ?string
    {
        if ([] !== AstFinder::calls($cond, ['istransactionactive'])) {
            return 'isTransactionActive()';
        }

        return [] !== AstFinder::calls($cond, ['gettransactionnestinglevel']) ? 'getTransactionNestingLevel()' : null;
    }
}

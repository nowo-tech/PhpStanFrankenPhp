<?php

declare(strict_types=1);

namespace NowoTech\PhpStanFrankenPhp\Rule\Worker;

use NowoTech\PhpStanFrankenPhp\Support\AstFinder;
use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\Closure;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\NullsafeMethodCall;
use PhpParser\Node\Expr\NullsafePropertyFetch;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Name;
use PhpParser\Node\Param;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Foreach_;
use PHPStan\Analyser\Scope;
use PHPStan\Node\InClassMethodNode;
use PHPStan\Rules\IdentifierRuleError;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleErrorBuilder;

/**
 * Level 2 (worker, opt-in) — flags recursion over a parent / child relation with no depth limit
 * and no visited set.
 *
 * A cycle in the data (a task that is its own ancestor) or a very deep tree makes the recursion
 * run until the worker hits the memory limit or overflows the C stack; on FrankenPHP the crash
 * takes down the worker thread and every request it was serving.
 *
 * Heuristic: a method that calls itself (`$this->m()`, `self::m()`, `static::m()`) or a closure
 * assigned to a variable it captures by reference (`$walk = function (…) use (&$walk)`) is
 * reported when one argument of the recursive call walks a relation; a method that calls the
 * same method on a related object (`$this->parent->m()`, `$child->m()`) is reported when that
 * receiver walks a relation. An expression walks a relation when it reads a property or calls
 * a method whose name has a relation word (child/children, parent, descendant, ancestor,
 * reply/replies, subtask, subpage, subfolder, subcategory, subcomment), directly or through the
 * `foreach` variable bound to such an expression. It is not reported when the method / closure
 * has a parameter (or captured variable) named like a guard: depth, level, limit, max, remaining,
 * visited, seen, budget, hops.
 *
 * Enable via `frankenphp.flagUnboundedRecursion: true` or `ruleset-worker-load.neon`.
 *
 * Runs per method (`InClassMethodNode`): trait methods are analysed in each using class.
 *
 * @implements Rule<InClassMethodNode>
 */
final class NoUnboundedRecursionRule implements Rule
{
    private const MESSAGE = '%s calls itself while walking %s with no depth limit and no visited set: a cycle in the data or a very deep tree recurses until the worker hits the memory limit or overflows the stack, and the crash takes down every request of that worker. Pass a depth counter (stop at a maximum) or a visited set, or walk iteratively with an explicit stack.';

    private const RELATION_TOKENS = [
        'child', 'children', 'parent', 'parents', 'descendant', 'descendants', 'ancestor', 'ancestors',
        'reply', 'replies', 'subtask', 'subtasks', 'subpage', 'subpages', 'subfolder', 'subfolders',
        'subcategory', 'subcategories', 'subcomment', 'subcomments',
    ];

    private const GUARD_TOKENS = ['depth', 'level', 'limit', 'max', 'remaining', 'visited', 'seen', 'budget', 'hops'];

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
        $this->checkMethod($node->getOriginalNode(), $errors);
        $this->checkClosures($node->getOriginalNode(), $errors);

        return $errors;
    }

    /**
     * @param list<IdentifierRuleError> $errors
     */
    private function checkMethod(ClassMethod $method, array &$errors): void
    {
        if ($this->guarded($method->params, [])) {
            return;
        }
        $name = $method->name->toLowerString();
        $calls = AstFinder::findAll($method, static fn (Node $n): bool => $name === AstFinder::callName($n)
            && ($n instanceof MethodCall || $n instanceof NullsafeMethodCall || self::isSelfCall($n)), true);
        $this->report($calls, $method, 'Method '.$method->name->toString().'()', $errors);
    }

    /**
     * Closures assigned to `$var` that capture `&$var` and call `$var(…)`.
     *
     * @param list<IdentifierRuleError> $errors
     */
    private function checkClosures(ClassMethod $method, array &$errors): void
    {
        $assigns = AstFinder::findAll($method, static fn (Node $n): bool => $n instanceof Assign && $n->expr instanceof Closure
            && $n->var instanceof Variable && \is_string($n->var->name), true);
        foreach ($assigns as $assign) {
            /** @var Assign $assign */
            /** @var Closure $closure */
            $closure = $assign->expr;
            /** @var string $var */
            $var = $assign->var instanceof Variable ? $assign->var->name : '';
            $captured = [];
            $recursive = false;
            foreach ($closure->uses as $use) {
                /** @var string $useName */
                $useName = $use->var->name;
                $captured[] = $useName;
                $recursive = $recursive || ($use->byRef && $useName === $var);
            }
            if (!$recursive || $this->guarded(array_merge($method->params, $closure->params), array_diff($captured, [$var]))) {
                continue;
            }
            $calls = AstFinder::findAll($closure, static fn (Node $n): bool => $n instanceof FuncCall && $n->name instanceof Variable
                && $n->name->name === $var, true);
            $this->report($calls, $closure, 'Closure $'.$var.' in '.$method->name->toString().'()', $errors);
        }
    }

    /**
     * @param list<Node>                $calls
     * @param list<IdentifierRuleError> $errors
     */
    private function report(array $calls, Node $function, string $subject, array &$errors): void
    {
        $bindings = [];
        foreach (AstFinder::findAll($function, static fn (Node $n): bool => $n instanceof Foreach_, true) as $foreach) {
            /** @var Foreach_ $foreach */
            if ($foreach->valueVar instanceof Variable && \is_string($foreach->valueVar->name)) {
                $bindings[$foreach->valueVar->name][] = $foreach->expr;
            }
        }
        foreach ($calls as $call) {
            /** @var MethodCall|NullsafeMethodCall|StaticCall|FuncCall $call */
            $walked = [];
            if (($call instanceof MethodCall || $call instanceof NullsafeMethodCall) && !self::isSelfCall($call)) {
                // `$this->parent->m()`, `$child->m()` in `foreach ($this->children as $child)`
                $walked[] = $call->var;
            } else {
                foreach ($call->args as $arg) {
                    if ($arg instanceof Arg) {
                        $walked[] = $arg->value;
                    }
                }
            }
            foreach ($walked as $expr) {
                $relation = $this->relation($expr, $bindings);
                if (null !== $relation) {
                    $errors[] = RuleErrorBuilder::message(\sprintf(self::MESSAGE, $subject, $relation))
                        ->identifier('frankenphp.worker.noUnboundedRecursion')
                        ->line($call->getStartLine())
                        ->build();

                    return;
                }
            }
        }
    }

    /**
     * The relation an argument walks (`childrenOf()`, `->parent`), or null.
     *
     * @param array<string, list<Expr>> $bindings foreach value variable => iterated expressions
     */
    private function relation(Expr $arg, array $bindings): ?string
    {
        $fetches = AstFinder::findAll($arg, static fn (Node $n): bool => $n instanceof PropertyFetch || $n instanceof NullsafePropertyFetch
            || $n instanceof MethodCall || $n instanceof NullsafeMethodCall);
        foreach ($fetches as $fetch) {
            $name = AstFinder::terminalName($fetch);
            if (null !== $name && [] !== array_intersect(AstFinder::tokens($name), self::RELATION_TOKENS)) {
                return $fetch instanceof PropertyFetch || $fetch instanceof NullsafePropertyFetch ? '->'.$name : $name.'()';
            }
        }
        if ($arg instanceof Variable && \is_string($arg->name)) {
            foreach ($bindings[$arg->name] ?? [] as $iterated) {
                $relation = $this->relation($iterated, []);
                if (null !== $relation) {
                    return $relation;
                }
            }
        }

        return null;
    }

    /**
     * @param array<Param>  $params
     * @param array<string> $captured
     */
    private function guarded(array $params, array $captured): bool
    {
        $names = $captured;
        foreach ($params as $param) {
            if ($param->var instanceof Variable && \is_string($param->var->name)) {
                $names[] = $param->var->name;
            }
        }
        foreach ($names as $name) {
            if ([] !== array_intersect(AstFinder::tokens($name), self::GUARD_TOKENS)) {
                return true;
            }
        }

        return false;
    }

    private static function isSelfCall(Node $call): bool
    {
        if ($call instanceof MethodCall || $call instanceof NullsafeMethodCall) {
            return $call->var instanceof Variable && 'this' === $call->var->name;
        }

        return $call instanceof StaticCall && $call->class instanceof Name
            && \in_array($call->class->toLowerString(), ['self', 'static'], true);
    }
}

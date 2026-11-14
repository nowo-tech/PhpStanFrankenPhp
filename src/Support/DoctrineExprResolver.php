<?php

declare(strict_types=1);

namespace NowoTech\PhpStanFrankenPhp\Support;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\ComplexType;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\New_;
use PhpParser\Node\Expr\NullsafeMethodCall;
use PhpParser\Node\Expr\NullsafePropertyFetch;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\FunctionLike;
use PhpParser\Node\Identifier;
use PhpParser\Node\IntersectionType;
use PhpParser\Node\Name;
use PhpParser\Node\NullableType;
use PhpParser\Node\Param;
use PhpParser\Node\UnionType;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\ClassReflection;
use PHPStan\Type\MixedType;
use PHPStan\Type\ObjectType;
use PHPStan\Type\Type;
use PHPStan\Type\TypeCombinator;

/**
 * Decides whether an expression is a Doctrine entity manager or DBAL connection, from PHPStan types
 * when the classes are known, from the declared class name when the class cannot be loaded
 * (analysis without the vendor tree), and from the variable / property / getter name when the
 * expression has no class type at all (mixed, untyped).
 *
 * Variables are resolved through the function's parameters (closures included) and the first
 * assignment to the same name, so `$connection = $this->em->getConnection();` resolves.
 */
final class DoctrineExprResolver
{
    public const KIND_MANAGER = 'manager';
    public const KIND_CONNECTION = 'connection';
    public const KIND_OTHER = 'other';
    public const KIND_UNKNOWN = 'unknown';

    private const MANAGER_CLASSES = [
        'Doctrine\\Persistence\\ObjectManager',
        'Doctrine\\ORM\\EntityManagerInterface',
    ];

    private const CONNECTION_CLASSES = [
        'Doctrine\\DBAL\\Connection',
    ];

    /** Receiver names taken for an entity manager when its type is unknown. */
    private const MANAGER_NAMES = [
        'em',
        'om',
        'entitymanager',
        'objectmanager',
        'manager',
        'getentitymanager',
        'getmanager',
        'getobjectmanager',
        'getmanagerforclass',
    ];

    /** Name tokens taken for a DBAL connection when the receiver type is unknown. */
    private const CONNECTION_NAME_TOKENS = ['connection', 'conn', 'db', 'dbal'];

    private const MAX_DEPTH = 8;

    /**
     * @param array<string, Type|Expr> $vars
     */
    private function __construct(
        private readonly Scope $scope,
        private readonly ?ClassReflection $class,
        private readonly array $vars,
    ) {
    }

    public static function forFunction(Scope $scope, ?ClassReflection $class, FunctionLike&Node $function): self
    {
        $vars = [];
        foreach (AstFinder::findAll($function, static fn (Node $n): bool => $n instanceof Param, true) as $param) {
            /** @var Param $param */
            if ($param->var instanceof Variable && \is_string($param->var->name) && null !== $param->type
                && !isset($vars[$param->var->name])
            ) {
                $vars[$param->var->name] = self::typeFromNode($scope, $param->type);
            }
        }
        $assigns = AstFinder::findAll(
            $function,
            static fn (Node $n): bool => $n instanceof Assign && $n->var instanceof Variable && \is_string($n->var->name),
            true,
        );
        foreach ($assigns as $assign) {
            /** @var Assign $assign */
            /** @var string $name */
            $name = $assign->var instanceof Variable ? $assign->var->name : '';
            $vars[$name] ??= $assign->expr;
        }

        return new self($scope, $class, $vars);
    }

    public function typeOf(Expr $expr, int $depth = 0): Type
    {
        if ($depth > self::MAX_DEPTH) {
            return new MixedType();
        }
        if ($expr instanceof Variable && \is_string($expr->name)) {
            if ('this' === $expr->name) {
                return $this->class instanceof ClassReflection ? new ObjectType($this->class->getName()) : new MixedType();
            }
            $bound = $this->vars[$expr->name] ?? null;
            if ($bound instanceof Type) {
                return $bound;
            }

            return $bound instanceof Expr ? $this->typeOf($bound, $depth + 1) : new MixedType();
        }
        if ($expr instanceof Assign) {
            return $this->typeOf($expr->expr, $depth + 1);
        }
        if ($expr instanceof New_ && $expr->class instanceof Name) {
            return new ObjectType($this->scope->resolveName($expr->class));
        }
        if (($expr instanceof PropertyFetch || $expr instanceof NullsafePropertyFetch) && $expr->name instanceof Identifier) {
            $holder = $this->typeOf($expr->var, $depth + 1);
            $name = $expr->name->toString();

            return $holder->hasInstanceProperty($name)->yes()
                ? $holder->getInstanceProperty($name, $this->scope)->getReadableType()
                : new MixedType();
        }
        if (($expr instanceof MethodCall || $expr instanceof NullsafeMethodCall) && $expr->name instanceof Identifier) {
            $holder = $this->typeOf($expr->var, $depth + 1);
            $name = $expr->name->toString();
            if ($holder->hasMethod($name)->yes()) {
                return $holder->getMethod($name, $this->scope)->getVariants()[0]->getReturnType();
            }

            return $this->wellKnownReturnType($expr->name->toLowerString());
        }

        return new MixedType();
    }

    /**
     * One of the KIND_* constants.
     */
    public function kindOf(Expr $expr): string
    {
        $type = $this->typeOf($expr);
        $names = $type->getObjectClassNames();
        if ([] === $names) {
            return self::KIND_UNKNOWN;
        }
        $reflections = [];
        foreach ($type->getObjectClassReflections() as $reflection) {
            $reflections[strtolower($reflection->getName())] = $reflection;
        }
        foreach ($names as $name) {
            $reflection = $reflections[strtolower($name)] ?? null;
            $kind = null === $reflection ? $this->kindFromClassName($name) : $this->kindFromReflection($reflection);
            if (self::KIND_OTHER !== $kind) {
                return $kind;
            }
        }

        return self::KIND_OTHER;
    }

    /**
     * Whether a call on `$receiver` can open / close a Doctrine transaction: a connection or a
     * manager, or an untyped receiver named like one (`$conn`, `getConnection()`, `$em`…).
     */
    public function isTransactional(Expr $receiver): bool
    {
        $kind = $this->kindOf($receiver);
        if (self::KIND_UNKNOWN !== $kind) {
            return self::KIND_OTHER !== $kind;
        }
        $tokens = AstFinder::tokens((string) AstFinder::terminalName($receiver));

        return [] !== array_intersect($tokens, self::CONNECTION_NAME_TOKENS) || $this->isManager($receiver);
    }

    /**
     * Whether `$expr` is an entity manager (typed as one, or untyped with a manager-like name).
     */
    public function isManager(Expr $expr): bool
    {
        $kind = $this->kindOf($expr);
        if (self::KIND_UNKNOWN !== $kind) {
            return self::KIND_MANAGER === $kind;
        }
        $name = AstFinder::terminalName($expr);
        if (null === $name) {
            return false;
        }
        $lower = strtolower($name);

        return \in_array($lower, self::MANAGER_NAMES, true)
            || str_ends_with($lower, 'entitymanager')
            || str_ends_with($lower, 'objectmanager');
    }

    /**
     * Whether a call is a transaction-control call (`$name`) on a connection / manager receiver.
     *
     * @param list<string> $names lower-case method names
     */
    public function isTransactionCall(Node $node, array $names): bool
    {
        return ($node instanceof MethodCall || $node instanceof NullsafeMethodCall)
            && \in_array(AstFinder::callName($node), $names, true)
            && $this->isTransactional($node->var);
    }

    /**
     * `direct` for `$entityManager->flush()`, `helper` for a `flush($entityManager)` helper
     * (e.g. a recovering flusher), null when the node is not a flush of an entity manager.
     */
    public function flushKind(Node $node): ?string
    {
        if ('flush' !== AstFinder::callName($node)) {
            return null;
        }
        /** @var MethodCall|NullsafeMethodCall|StaticCall $node */
        if (!$node instanceof StaticCall && $this->isManager($node->var)) {
            return 'direct';
        }
        foreach ($node->args as $arg) {
            if ($arg instanceof Arg && $this->isManager($arg->value)) {
                return 'helper';
            }
        }

        return null;
    }

    private function wellKnownReturnType(string $method): Type
    {
        return match ($method) {
            'getconnection' => new ObjectType('Doctrine\\DBAL\\Connection'),
            'getentitymanager' => new ObjectType('Doctrine\\ORM\\EntityManagerInterface'),
            'getmanager', 'getmanagerforclass', 'getobjectmanager' => new ObjectType('Doctrine\\Persistence\\ObjectManager'),
            default => new MixedType(),
        };
    }

    private static function typeFromNode(Scope $scope, Identifier|Name|ComplexType $node): Type
    {
        if ($node instanceof NullableType) {
            return self::typeFromNode($scope, $node->type);
        }
        if ($node instanceof UnionType || $node instanceof IntersectionType) {
            $types = [];
            foreach ($node->types as $inner) {
                $types[] = self::typeFromNode($scope, $inner);
            }

            return TypeCombinator::union(...$types);
        }

        return $node instanceof Name ? new ObjectType($scope->resolveName($node)) : new MixedType();
    }

    private function kindFromReflection(ClassReflection $reflection): string
    {
        foreach (self::MANAGER_CLASSES as $class) {
            if ($reflection->getName() === $class || $reflection->isSubclassOf($class)) {
                return self::KIND_MANAGER;
            }
        }
        foreach (self::CONNECTION_CLASSES as $class) {
            if ($reflection->getName() === $class || $reflection->isSubclassOf($class)) {
                return self::KIND_CONNECTION;
            }
        }

        return self::KIND_OTHER;
    }

    /**
     * Class not loadable in this analysis (no vendor tree): decide by its name. A declared class
     * that is not named like a Doctrine manager / connection is taken for something else.
     */
    private function kindFromClassName(string $name): string
    {
        $lower = strtolower($name);
        if (str_ends_with($lower, 'entitymanagerinterface') || str_ends_with($lower, 'entitymanager')
            || str_ends_with($lower, 'objectmanager')
        ) {
            return self::KIND_MANAGER;
        }
        if (str_starts_with($lower, 'doctrine\\dbal\\') && str_ends_with($lower, 'connection')) {
            return self::KIND_CONNECTION;
        }

        return self::KIND_OTHER;
    }
}

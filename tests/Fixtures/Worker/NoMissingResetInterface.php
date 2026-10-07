<?php

declare(strict_types=1);

namespace DemoWorker;

use Symfony\Contracts\Service\ResetInterface;

final class NoMissingResetInterface
{
    private int $hits = 0;

    public function hit(): void
    {
        ++$this->hits; // error
    }
}

final class NoMissingResetInterfaceWithReset implements ResetInterface
{
    private int $hits = 0;

    public function hit(): void
    {
        ++$this->hits;
    }

    public function reset(): void
    {
        $this->hits = 0;
    }
}

final class CachedEntity
{
    private string $name = '';

    public function rename(string $name): void
    {
        $this->name = $name; // skipped by Entity suffix
    }
}

final class ConstructOnly
{
    private int $n;

    public function __construct(int $n)
    {
        $this->n = $n;
    }
}

final class AssignOpMutator
{
    private int $n = 0;

    public function bump(): void
    {
        ++$this->n; // error AssignOp
    }

    public function set(): void
    {
        $this->n = 1; // error Assign
    }
}

final class LocalNoiseOnly
{
    public function noop(): void
    {
        $x = 1;
        $y = 0;
        ++$y;
        if (true) {
            $z = 2;
        }
    }
}

abstract class AbstractLeaky
{
    private int $n = 0;

    public function bump(): void
    {
        ++$this->n; // skipped: abstract class
    }
}

final class ArrayWrite
{
    /** @var array<int, string> */
    private array $tokens = [];

    public function get(int $id): string
    {
        if (!isset($this->tokens[$id])) {
            $this->tokens[$id] = (string) $id; // error: ArrayDimFetch write
        }

        return $this->tokens[$id];
    }
}

final class ArrayAppend
{
    /** @var list<string> */
    private array $items = [];

    public function add(string $item): void
    {
        $this->items[] = $item; // error: ArrayDimFetch append
    }
}

final class NestedArrayWrite
{
    /** @var array<string, array<string, int>> */
    private array $matrix = [];

    public function set(string $k, string $j, int $v): void
    {
        $this->matrix[$k][$j] = $v; // error: nested ArrayDimFetch write
    }
}

final class ArrayAssignOp
{
    /** @var array<string, int> */
    private array $counters = [];

    public function bump(string $k): void
    {
        $this->counters[$k] += 2; // error: AssignOp on ArrayDimFetch
    }
}

final class ArrayCoalesceAssign
{
    /** @var array<string, string> */
    private array $items = [];

    public function remember(string $k, string $v): void
    {
        $this->items[$k] ??= $v; // error: AssignOp\Coalesce on ArrayDimFetch
    }
}

final class ArrayIncDec
{
    /** @var array<string, int> */
    private array $counters = [];

    public function up(string $k): void
    {
        ++$this->counters[$k]; // error: PreInc on ArrayDimFetch
    }

    public function down(string $k): void
    {
        $previous = $this->counters[$k]--; // error: PostDec on ArrayDimFetch
    }
}

final class ArrayUnset
{
    /** @var array<string, string> */
    private array $items = [];

    public function forget(string $k): void
    {
        unset($this->items[$k]); // error: unset on ArrayDimFetch
    }
}

final class NestedPropertyWrite
{
    private \stdClass $config;

    public function __construct()
    {
        $this->config = new \stdClass();
    }

    public function set(string $v): void
    {
        $this->config->value = $v; // error: nested PropertyFetch write
    }
}

final class NestedPropertyArrayWrite
{
    private \stdClass $config;

    public function __construct()
    {
        $this->config = new \stdClass();
        $this->config->values = [];
    }

    public function set(string $k, string $v): void
    {
        $this->config->values[$k] = $v; // error: ArrayDimFetch on nested PropertyFetch
    }
}

final class AssignByRef
{
    /** @var array<string, string> */
    private array $items = [];

    public function bind(array &$source): void
    {
        $this->items = &$source; // error: AssignRef
    }
}

final class ReadOnlyAccess
{
    /** @var array<string, string> */
    private array $items = [];

    private \stdClass $config;

    public function __construct()
    {
        $this->config = new \stdClass();
    }

    public function has(string $k): bool
    {
        return isset($this->items[$k]);
    }

    public function first(): ?string
    {
        foreach ($this->items as $item) {
            return $item;
        }

        return null;
    }

    public function copy(string $k): string
    {
        $x = $this->items[$k];
        $local = [];
        $local[$k] = $x;
        $other = new \stdClass();
        $other->value = $this->config->value;
        $other->list[$k] = $x;
        unset($local[$k], $other);

        return $x;
    }
}

final class CheckoutForm
{
    private string $step = 'cart';

    public function next(string $step): void
    {
        $this->step = $step; // skipped by Form suffix
    }
}

final class AddressFormType
{
    /** @var array<string, mixed> */
    private array $options = [];

    public function setDefault(string $k, mixed $v): void
    {
        $this->options[$k] = $v; // skipped by FormType suffix
    }
}

final class MoneyDataTransformer
{
    private bool $dirty = false;

    public function mark(): void
    {
        $this->dirty = true; // skipped by DataTransformer suffix
    }
}

final class PriceTypeExtension
{
    private bool $configured = false;

    public function configure(): void
    {
        $this->configured = true; // skipped by TypeExtension suffix
    }
}

final class LineItemDataMapper
{
    private bool $mapped = false;

    public function map(): void
    {
        $this->mapped = true; // skipped by DataMapper suffix
    }
}

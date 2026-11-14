<?php

declare(strict_types=1);

namespace DemoWorker\Recursion;

final class Task
{
    private ?Task $parent = null;

    /** @var list<Task> */
    private array $children = [];

    public function getId(): string
    {
        return 'id';
    }

    public function getParent(): ?Task
    {
        return $this->parent;
    }

    /**
     * @return list<Task>
     */
    public function getChildren(): array
    {
        return $this->children;
    }

    public function path(): string
    {
        return ($this->parent?->path() ?? '').'/'.$this->getId(); // error: same method on ->parent
    }

    public function count(): int
    {
        $total = 1;
        foreach ($this->children as $child) {
            $total += $child->count(); // error: same method on a foreach child
        }

        return $total;
    }
}

// Modelled on TaskBoard TaskTree (1.15).
final class TaskTree
{
    /**
     * @param array<string, list<Task>> $children
     */
    public function __construct(
        private array $children,
    ) {
    }

    /**
     * @return list<Task>
     */
    public function childrenOf(Task $task): array
    {
        return $this->children[$task->getId()] ?? [];
    }

    /**
     * @return list<Task>
     */
    public function descendantsOf(Task $task): array
    {
        $descendants = [];
        foreach ($this->childrenOf($task) as $child) {
            $descendants[] = $child;
            foreach ($this->descendantsOf($child) as $nested) { // error
                $descendants[] = $nested;
            }
        }

        return $descendants;
    }

    /**
     * @return list<Task>
     */
    public function boundedDescendantsOf(Task $task, int $maxDepth = 10): array
    {
        if (0 === $maxDepth) {
            return [];
        }
        $descendants = [];
        foreach ($this->childrenOf($task) as $child) {
            $descendants = [...$descendants, $child, ...$this->boundedDescendantsOf($child, $maxDepth - 1)]; // fine: depth
        }

        return $descendants;
    }

    /**
     * @param array<string, true> $visited
     *
     * @return list<string>
     */
    public function ancestorIds(Task $task, array $visited = []): array
    {
        $parent = $task->getParent();
        if (null === $parent || isset($visited[$parent->getId()])) {
            return [];
        }
        $visited[$parent->getId()] = true;

        return [$parent->getId(), ...$this->ancestorIds($parent, $visited)]; // fine: visited set
    }

    public static function rootOf(Task $task): Task
    {
        $parent = $task->getParent();

        return null === $parent ? $task : self::rootOf($task->getParent()); // error: static, ->getParent()
    }

    public function factorial(int $n): int
    {
        return $n <= 1 ? 1 : $n * $this->factorial($n - 1); // fine: no relation
    }

    public function sameNameOnOther(Task $task, self $other): int
    {
        return $other->sameNameOnOther($task, $other); // fine: receiver is not a relation
    }
}

final class Replies
{
    /**
     * @return list<string>
     */
    public function flatten(object $comment): array
    {
        $out = [];
        $walk = function (object $node) use (&$walk, &$out): void {
            foreach ($node->getReplies() as $reply) {
                $out[] = $reply;
                $walk($reply); // error: recursive closure over getReplies()
            }
        };
        $walk($comment);

        return $out;
    }

    /**
     * @return list<string>
     */
    public function flattenGuarded(object $comment): array
    {
        $seen = [];
        $walk = function (object $node) use (&$walk, &$seen): void {
            foreach ($node->getReplies() as $reply) {
                $seen[] = $reply;
                $walk($reply); // fine: captures a seen set
            }
        };
        $walk($comment);
        $notRecursive = function (object $node) use ($walk): void {
            $walk($node->getReplies());
        };
        $notRecursive($comment);
        $constant = function (int $n) use (&$constant): int {
            return $n > 0 ? $constant($n - 1) : 0; // fine: no relation
        };

        return [] === $seen ? [] : [(string) $constant(1)];
    }
}

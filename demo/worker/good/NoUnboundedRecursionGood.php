<?php

declare(strict_types=1);

namespace DemoWorker\Good;

final class NoUnboundedRecursionGood
{
    /**
     * @param array<string, list<string>> $children
     */
    public function __construct(
        private array $children,
    ) {
    }

    /**
     * @return list<string>
     */
    public function childrenOf(string $id): array
    {
        return $this->children[$id] ?? [];
    }

    /**
     * @param array<string, true> $visited
     *
     * @return list<string>
     */
    public function descendantsOf(string $id, array $visited = []): array
    {
        $descendants = [];
        foreach ($this->childrenOf($id) as $child) {
            if (isset($visited[$child])) {
                continue;
            }
            $visited[$child] = true;
            $descendants = [...$descendants, $child, ...$this->descendantsOf($child, $visited)];
        }

        return $descendants;
    }
}

<?php

declare(strict_types=1);

namespace DemoWorker;

final class NoUnboundedRecursion
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
     * @return list<string>
     */
    public function descendantsOf(string $id): array
    {
        $descendants = [];
        foreach ($this->childrenOf($id) as $child) {
            $descendants = [...$descendants, $child, ...$this->descendantsOf($child)]; // error: a cycle never ends
        }

        return $descendants;
    }
}

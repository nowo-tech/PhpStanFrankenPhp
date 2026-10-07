<?php

declare(strict_types=1);

namespace DemoWorker\Good;

use Symfony\Contracts\Service\ResetInterface;

final class NoMissingResetInterfaceArrayWriteGood implements ResetInterface
{
    /** @var array<int, string> */
    private array $tokens = [];

    public function get(int $id): string
    {
        if (!isset($this->tokens[$id])) {
            $this->tokens[$id] = $this->retrieve($id);
        }

        return $this->tokens[$id];
    }

    public function reset(): void
    {
        $this->tokens = [];
    }

    private function retrieve(int $id): string
    {
        return 'token-'.$id;
    }
}

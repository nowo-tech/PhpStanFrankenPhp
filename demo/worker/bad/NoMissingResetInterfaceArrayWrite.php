<?php

declare(strict_types=1);

namespace DemoWorker;

final class NoMissingResetInterfaceArrayWrite
{
    /** @var array<int, string> */
    private array $tokens = [];

    public function get(int $id): string
    {
        if (!isset($this->tokens[$id])) {
            $this->tokens[$id] = $this->retrieve($id); // error: array element write survives across requests
        }

        return $this->tokens[$id];
    }

    private function retrieve(int $id): string
    {
        return 'token-'.$id;
    }
}

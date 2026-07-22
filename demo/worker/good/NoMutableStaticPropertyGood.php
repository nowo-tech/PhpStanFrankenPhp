<?php

declare(strict_types=1);

namespace DemoWorker\Good;

use Symfony\Contracts\Service\ResetInterface;

final class NoMutableStaticPropertyGood implements ResetInterface
{
    private const DEFAULT_LOCALE = 'en';

    private ?string $locale = null;

    public function setLocale(string $locale): void
    {
        $this->locale = $locale;
    }

    public function getLocale(): string
    {
        return $this->locale ?? self::DEFAULT_LOCALE;
    }

    public function reset(): void
    {
        $this->locale = null;
    }
}

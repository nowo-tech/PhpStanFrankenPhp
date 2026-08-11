<?php

declare(strict_types=1);

namespace DemoWorker\Good;

final class NoSetLocaleGood
{
    public function formatMoney(string $locale, float $amount): string
    {
        // Prefer request/framework locale (Symfony Request::setLocale / Translator), not setlocale().
        return \sprintf('[%s] %.2f', $locale, $amount);
    }

    public function currentLocale(): string|false
    {
        // Query-only: setlocale($category, 0) does not mutate process locale.
        return setlocale(\LC_ALL, 0);
    }
}

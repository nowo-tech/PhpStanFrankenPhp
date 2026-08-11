<?php

declare(strict_types=1);

namespace DemoWorker\Good;

final class NoLocaleSetDefaultGood
{
    public function label(string $requestLocale): string
    {
        // Prefer request/framework locale (Symfony Request::setLocale / Translator).
        return strtoupper($requestLocale);
    }

    public function currentDefault(): string
    {
        return locale_get_default();
    }
}

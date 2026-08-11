<?php

declare(strict_types=1);

namespace DemoWorker;

final class NoLocaleSetDefault
{
    public function bad(): void
    {
        locale_set_default('en_US'); // error
        \Locale::setDefault('es_ES'); // error
    }

    public function readsAreAllowed(): void
    {
        locale_get_default();
        \Locale::getDefault();
    }
}

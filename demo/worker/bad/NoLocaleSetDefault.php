<?php

declare(strict_types=1);

namespace DemoWorker;

use Locale;

final class NoLocaleSetDefault
{
    public function bad(): void
    {
        locale_set_default('en_US'); // error
        Locale::setDefault('es_ES'); // error
    }
}

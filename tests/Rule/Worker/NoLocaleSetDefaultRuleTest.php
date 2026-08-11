<?php

declare(strict_types=1);

namespace NowoTech\PhpStanFrankenPhp\Tests\Rule\Worker;

use NowoTech\PhpStanFrankenPhp\Rule\Worker\NoLocaleSetDefaultRule;
use NowoTech\PhpStanFrankenPhp\Tests\Rule\AbstractRuleTestCase;
use PHPStan\Rules\Rule;

/**
 * @extends AbstractRuleTestCase<NoLocaleSetDefaultRule>
 */
final class NoLocaleSetDefaultRuleTest extends AbstractRuleTestCase
{
    protected function getRule(): Rule
    {
        return new NoLocaleSetDefaultRule();
    }

    public function testRule(): void
    {
        $suffix = ' mutates the process-wide ICU default locale that persists on FrankenPHP workers. Prefer request/framework locale (e.g. Symfony Request::setLocale / Translator) or configure intl defaults in php.ini / the image.';

        $this->analyse([__DIR__.'/../../Fixtures/Worker/NoLocaleSetDefault.php'], [
            ['locale_set_default()'.$suffix, 11],
            ['Locale::setDefault()'.$suffix, 12],
        ]);
    }
}

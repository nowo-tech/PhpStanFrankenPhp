<?php

declare(strict_types=1);

namespace NowoTech\PhpStanFrankenPhp\Tests\Rule\Worker;

use NowoTech\PhpStanFrankenPhp\Rule\Worker\NoSetLocaleRule;
use NowoTech\PhpStanFrankenPhp\Tests\Rule\AbstractRuleTestCase;
use PHPStan\Rules\Rule;

/**
 * @extends AbstractRuleTestCase<NoSetLocaleRule>
 */
final class NoSetLocaleRuleTest extends AbstractRuleTestCase
{
    protected function getRule(): Rule
    {
        return new NoSetLocaleRule();
    }

    public function testRule(): void
    {
        $this->analyse([__DIR__.'/../../Fixtures/Worker/NoSetLocale.php'], [
            [
                'setlocale() with a locale argument mutates process-wide locale state that persists on FrankenPHP workers. Prefer framework request locale (e.g. Symfony Translator / Request::setLocale) or configure locale in php.ini / the image. Queries via setlocale($category, 0) stay allowed.',
                11,
            ],
        ]);
    }
}

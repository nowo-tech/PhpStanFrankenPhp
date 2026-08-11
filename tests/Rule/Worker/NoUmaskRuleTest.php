<?php

declare(strict_types=1);

namespace NowoTech\PhpStanFrankenPhp\Tests\Rule\Worker;

use NowoTech\PhpStanFrankenPhp\Rule\Worker\NoUmaskRule;
use NowoTech\PhpStanFrankenPhp\Tests\Rule\AbstractRuleTestCase;
use PHPStan\Rules\Rule;

/**
 * @extends AbstractRuleTestCase<NoUmaskRule>
 */
final class NoUmaskRuleTest extends AbstractRuleTestCase
{
    protected function getRule(): Rule
    {
        return new NoUmaskRule();
    }

    public function testRule(): void
    {
        $this->analyse([__DIR__.'/../../Fixtures/Worker/NoUmask.php'], [
            [
                'umask() with a mask argument changes the process file-creation mask and persists on FrankenPHP workers. Set umask in the process supervisor / container entrypoint instead of per request.',
                11,
            ],
        ]);
    }
}

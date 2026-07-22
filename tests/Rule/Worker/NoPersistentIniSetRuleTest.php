<?php

declare(strict_types=1);

namespace NowoTech\PhpStanFrankenPhp\Tests\Rule\Worker;

use NowoTech\PhpStanFrankenPhp\Rule\Worker\NoPersistentIniSetRule;
use NowoTech\PhpStanFrankenPhp\Tests\Rule\AbstractRuleTestCase;
use PHPStan\Rules\Rule;

/**
 * @extends AbstractRuleTestCase<NoPersistentIniSetRule>
 */
final class NoPersistentIniSetRuleTest extends AbstractRuleTestCase
{
    protected function getRule(): Rule
    {
        return new NoPersistentIniSetRule();
    }

    public function testRule(): void
    {
        $this->analyse([__DIR__.'/../../Fixtures/Worker/NoPersistentIniSet.php'], [
            [
                "ini_set('memory_limit', …) changes a process-wide setting that persists on FrankenPHP workers and affects later requests. Configure it in php.ini / the FrankenPHP image instead.",
                11,
            ],
        ]);
    }

    public function testExtraVariants(): void
    {
        $this->analyse([__DIR__.'/../../Fixtures/Worker/NoPersistentIniSetExtra.php'], [
            [
                'ini_set() may change process-wide settings that persist on FrankenPHP workers. Prefer php.ini / FrankenPHP image config, or limit changes to request-safe keys with an explicit reset.',
                11,
            ],
        ]);
    }
}

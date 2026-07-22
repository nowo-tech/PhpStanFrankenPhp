<?php

declare(strict_types=1);

namespace NowoTech\PhpStanFrankenPhp\Tests\Rule\Worker;

use NowoTech\PhpStanFrankenPhp\Rule\Worker\NoSingletonGetInstanceRule;
use NowoTech\PhpStanFrankenPhp\Tests\Rule\AbstractRuleTestCase;
use PHPStan\Rules\Rule;

/**
 * @extends AbstractRuleTestCase<NoSingletonGetInstanceRule>
 */
final class NoSingletonGetInstanceRuleTest extends AbstractRuleTestCase
{
    protected function getRule(): Rule
    {
        return new NoSingletonGetInstanceRule();
    }

    public function testRule(): void
    {
        $this->analyse([__DIR__.'/../../Fixtures/Worker/NoSingletonGetInstance.php'], [
            [
                'DemoWorker\Registry::getInstance() singletons retain state across FrankenPHP worker requests. Prefer container-managed services (and ResetInterface when they hold request data).',
                19,
            ],
        ]);
    }

    public function testExtraVariants(): void
    {
        $this->analyse([__DIR__.'/../../Fixtures/Worker/NoSingletonGetInstanceExtra.php'], [
            [
                'getInstance() singleton access retains state across FrankenPHP worker requests. Prefer container-managed services.',
                19,
            ],
        ]);
    }
}

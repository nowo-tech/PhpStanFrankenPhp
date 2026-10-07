<?php

declare(strict_types=1);

namespace NowoTech\PhpStanFrankenPhp\Tests\Rule\Worker;

use NowoTech\PhpStanFrankenPhp\Rule\Worker\NoMissingResetInterfaceRule;
use NowoTech\PhpStanFrankenPhp\Tests\Rule\AbstractRuleTestCase;
use PhpParser\Node\Stmt\Class_;
use PHPStan\Rules\Rule;
use PHPUnit\Framework\TestCase;

/**
 * @extends AbstractRuleTestCase<NoMissingResetInterfaceRule>
 */
final class NoMissingResetInterfaceRuleTest extends AbstractRuleTestCase
{
    protected function getRule(): Rule
    {
        return new NoMissingResetInterfaceRule(true);
    }

    public function testRule(): void
    {
        $msg = ' mutates instance state outside __construct/reset but does not implement Symfony\\Contracts\\Service\\ResetInterface. Under FrankenPHP worker with FRANKENPHP_RESET_KERNEL unset/false the kernel is reused; implement reset() (or keep the service stateless) so services_resetter clears request data.';

        $this->analyse([
            __DIR__.'/../../Fixtures/Worker/NoMissingResetInterface.php',
            __DIR__.'/../../Fixtures/Worker/NoMissingResetInterfaceEntityNs.php',
        ], [
            ['Class DemoWorker\\NoMissingResetInterface'.$msg, 9],
            ['Class DemoWorker\\AssignOpMutator'.$msg, 54],
            ['Class DemoWorker\\ArrayWrite'.$msg, 92],
            ['Class DemoWorker\\ArrayAppend'.$msg, 107],
            ['Class DemoWorker\\NestedArrayWrite'.$msg, 118],
            ['Class DemoWorker\\ArrayAssignOp'.$msg, 129],
            ['Class DemoWorker\\ArrayCoalesceAssign'.$msg, 140],
            ['Class DemoWorker\\ArrayIncDec'.$msg, 151],
            ['Class DemoWorker\\ArrayUnset'.$msg, 167],
            ['Class DemoWorker\\NestedPropertyWrite'.$msg, 178],
            ['Class DemoWorker\\NestedPropertyArrayWrite'.$msg, 193],
            ['Class DemoWorker\\AssignByRef'.$msg, 209],
        ]);
    }
}

final class NoMissingResetInterfaceRuleDisabledTest extends TestCase
{
    public function testDisabledProducesNoErrors(): void
    {
        $rule = new NoMissingResetInterfaceRule(false);
        self::assertSame(Class_::class, $rule->getNodeType());
    }
}

<?php

declare(strict_types=1);

namespace NowoTech\PhpStanFrankenPhp\Tests\Rule\Worker;

use NowoTech\PhpStanFrankenPhp\Rule\Worker\NoFlushWithoutClosedManagerRecoveryRule;
use NowoTech\PhpStanFrankenPhp\Tests\Rule\AbstractRuleTestCase;
use PHPStan\Rules\Rule;

/**
 * @extends AbstractRuleTestCase<NoFlushWithoutClosedManagerRecoveryRule>
 */
final class NoFlushWithoutClosedManagerRecoveryRuleTest extends AbstractRuleTestCase
{
    private bool $enabled = true;

    protected function getRule(): Rule
    {
        return new NoFlushWithoutClosedManagerRecoveryRule($this->enabled);
    }

    public function testRule(): void
    {
        $message = static fn (string $class, string $namespace = 'DemoWorker\\Db\\Recovery\\'): string => 'Class '.$namespace.$class.' calls EntityManager::flush() but never recovers a closed entity manager (no isOpen() check, no ManagerRegistry::resetManager(), no recovering helper). A failed flush closes the manager: every later write through it in the same request or message fails, and in a worker without DoctrineBundle\'s resetter it stays closed for later requests too. Check isOpen() and reset the manager through the registry after a failure, or flush through a recovering helper / TransactionRunnerInterface (nowo-tech/transaction-contract).';

        $this->analyse([
            __DIR__.'/../../Fixtures/Worker/NoFlushWithoutClosedManagerRecovery.php',
            __DIR__.'/../../Fixtures/Worker/NoFlushWithoutClosedManagerRecoveryFixturesNs.php',
            __DIR__.'/../../Fixtures/Worker/NoFlushWithoutClosedManagerRecoveryCqrs.php',
        ], [
            [$message('ChatService'), 82],
            [$message('ChatService'), 87],
            [$message('ChatService'), 92],
            [$message('RegistryManager'), 171],
            [$message('CreateTaskHandler', 'DemoWorker\\Application\\Command\\'), 20],
        ]);
    }

    public function testResolverCases(): void
    {
        $message = 'Class DemoWorker\\Db\\Resolver\\ResolverCases calls EntityManager::flush() but never recovers a closed entity manager (no isOpen() check, no ManagerRegistry::resetManager(), no recovering helper). A failed flush closes the manager: every later write through it in the same request or message fails, and in a worker without DoctrineBundle\'s resetter it stays closed for later requests too. Check isOpen() and reset the manager through the registry after a failure, or flush through a recovering helper / TransactionRunnerInterface (nowo-tech/transaction-contract).';

        $this->analyse([__DIR__.'/../../Fixtures/Worker/DoctrineExprResolverCases.php'], [
            [$message, 33],
            [$message, 38],
            [$message, 47],
            [$message, 49],
            [$message, 57],
            [$message, 65],
            [$message, 66],
        ]);
    }

    public function testDisabled(): void
    {
        $this->enabled = false;
        $this->analyse([
            __DIR__.'/../../Fixtures/Worker/NoFlushWithoutClosedManagerRecovery.php',
            __DIR__.'/../../Fixtures/Worker/NoFlushWithoutClosedManagerRecoveryFixturesNs.php',
            __DIR__.'/../../Fixtures/Worker/NoFlushWithoutClosedManagerRecoveryCqrs.php',
        ], []);
    }
}

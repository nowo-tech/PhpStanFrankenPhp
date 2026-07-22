<?php

declare(strict_types=1);

namespace NowoTech\PhpStanFrankenPhp\Tests\Rule\Classic;

use NowoTech\PhpStanFrankenPhp\Rule\Classic\NoExitOrDieRule;
use NowoTech\PhpStanFrankenPhp\Tests\Rule\AbstractRuleTestCase;
use PHPStan\Rules\Rule;

/**
 * @extends AbstractRuleTestCase<NoExitOrDieRule>
 */
final class NoExitOrDieRuleTest extends AbstractRuleTestCase
{
    protected function getRule(): Rule
    {
        return new NoExitOrDieRule();
    }

    public function testRule(): void
    {
        $this->analyse([__DIR__.'/../../Fixtures/Classic/NoExitOrDie.php'], [
            [
                'Do not use exit/die under FrankenPHP: it terminates the PHP process (or worker thread), not a single FPM request. Throw an exception or return an HTTP response instead.',
                11,
            ],
            [
                'Do not use exit/die under FrankenPHP: it terminates the PHP process (or worker thread), not a single FPM request. Throw an exception or return an HTTP response instead.',
                16,
            ],
        ]);
    }
}

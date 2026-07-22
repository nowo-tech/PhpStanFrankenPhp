<?php

declare(strict_types=1);

namespace NowoTech\PhpStanFrankenPhp\Tests\Rule\Worker;

use NowoTech\PhpStanFrankenPhp\Rule\Worker\NoSetErrorExceptionHandlerRule;
use NowoTech\PhpStanFrankenPhp\Tests\Rule\AbstractRuleTestCase;
use PHPStan\Rules\Rule;

/**
 * @extends AbstractRuleTestCase<NoSetErrorExceptionHandlerRule>
 */
final class NoSetErrorExceptionHandlerRuleTest extends AbstractRuleTestCase
{
    protected function getRule(): Rule
    {
        return new NoSetErrorExceptionHandlerRule();
    }

    public function testRule(): void
    {
        $this->analyse([__DIR__.'/../../Fixtures/Worker/NoSetErrorExceptionHandler.php'], [
            [
                'set_error_handler() registers a process-wide handler that persists on FrankenPHP workers. Prefer framework error/exception listeners and restore previous handlers if you must use natives temporarily.',
                11,
            ],
            [
                'set_exception_handler() registers a process-wide handler that persists on FrankenPHP workers. Prefer framework error/exception listeners and restore previous handlers if you must use natives temporarily.',
                12,
            ],
        ]);
    }
}

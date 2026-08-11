<?php

declare(strict_types=1);

namespace NowoTech\PhpStanFrankenPhp\Tests\Rule\Worker;

use NowoTech\PhpStanFrankenPhp\Rule\Worker\NoErrorReportingMutationRule;
use NowoTech\PhpStanFrankenPhp\Tests\Rule\AbstractRuleTestCase;
use PHPStan\Rules\Rule;

/**
 * @extends AbstractRuleTestCase<NoErrorReportingMutationRule>
 */
final class NoErrorReportingMutationRuleTest extends AbstractRuleTestCase
{
    protected function getRule(): Rule
    {
        return new NoErrorReportingMutationRule();
    }

    public function testRule(): void
    {
        $this->analyse([__DIR__.'/../../Fixtures/Worker/NoErrorReportingMutation.php'], [
            [
                'error_reporting() with a level argument changes process-wide error reporting that persists on FrankenPHP workers. Configure error_reporting in php.ini / the FrankenPHP image instead.',
                11,
            ],
        ]);
    }
}

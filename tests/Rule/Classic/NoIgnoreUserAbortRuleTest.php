<?php

declare(strict_types=1);

namespace NowoTech\PhpStanFrankenPhp\Tests\Rule\Classic;

use NowoTech\PhpStanFrankenPhp\Rule\Classic\NoIgnoreUserAbortRule;
use NowoTech\PhpStanFrankenPhp\Tests\Rule\AbstractRuleTestCase;
use PHPStan\Rules\Rule;

/**
 * @extends AbstractRuleTestCase<NoIgnoreUserAbortRule>
 */
final class NoIgnoreUserAbortRuleTest extends AbstractRuleTestCase
{
    protected function getRule(): Rule
    {
        return new NoIgnoreUserAbortRule();
    }

    public function testRule(): void
    {
        $this->analyse([__DIR__.'/../../Fixtures/Classic/NoIgnoreUserAbort.php'], [
            [
                'ignore_user_abort(true) keeps work running after disconnect and can pin a FrankenPHP thread. Use a queue for background work instead.',
                11,
            ],
        ]);
    }

    public function testExtraVariants(): void
    {
        $this->analyse([__DIR__.'/../../Fixtures/Classic/NoIgnoreUserAbortExtra.php'], [
            [
                'ignore_user_abort(true) keeps work running after disconnect and can pin a FrankenPHP thread. Use a queue for background work instead.',
                11,
            ],
            [
                'ignore_user_abort(true) keeps work running after disconnect and can pin a FrankenPHP thread. Use a queue for background work instead.',
                16,
            ],
        ]);
    }
}

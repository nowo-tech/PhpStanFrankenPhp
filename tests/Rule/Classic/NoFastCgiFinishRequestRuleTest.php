<?php

declare(strict_types=1);

namespace NowoTech\PhpStanFrankenPhp\Tests\Rule\Classic;

use NowoTech\PhpStanFrankenPhp\Rule\Classic\NoFastCgiFinishRequestRule;
use NowoTech\PhpStanFrankenPhp\Tests\Rule\AbstractRuleTestCase;
use PHPStan\Rules\Rule;

/**
 * @extends AbstractRuleTestCase<NoFastCgiFinishRequestRule>
 */
final class NoFastCgiFinishRequestRuleTest extends AbstractRuleTestCase
{
    protected function getRule(): Rule
    {
        return new NoFastCgiFinishRequestRule();
    }

    public function testRule(): void
    {
        $this->analyse([__DIR__.'/../../Fixtures/Classic/NoFastCgiFinishRequest.php'], [
            [
                'fastcgi_finish_request() is PHP-FPM/CGI specific and is not available under FrankenPHP. Flush the framework response and use async/messaging for post-response work instead.',
                11,
            ],
        ]);
    }
}

<?php

declare(strict_types=1);

namespace NowoTech\PhpStanFrankenPhp\Tests\Rule\Worker;

use NowoTech\PhpStanFrankenPhp\Rule\Worker\NoMbEncodingMutationRule;
use NowoTech\PhpStanFrankenPhp\Tests\Rule\AbstractRuleTestCase;
use PHPStan\Rules\Rule;

/**
 * @extends AbstractRuleTestCase<NoMbEncodingMutationRule>
 */
final class NoMbEncodingMutationRuleTest extends AbstractRuleTestCase
{
    protected function getRule(): Rule
    {
        return new NoMbEncodingMutationRule();
    }

    public function testRule(): void
    {
        $suffix = ' with an argument mutates process-wide mbstring state that persists on FrankenPHP workers. Configure mbstring in php.ini / the FrankenPHP image, or pass encodings/language explicitly to mb_* functions.';

        $this->analyse([__DIR__.'/../../Fixtures/Worker/NoMbEncodingMutation.php'], [
            ['mb_internal_encoding()'.$suffix, 11],
            ['mb_regex_encoding()'.$suffix, 12],
            ['mb_http_output()'.$suffix, 13],
            ['mb_language()'.$suffix, 14],
        ]);
    }
}

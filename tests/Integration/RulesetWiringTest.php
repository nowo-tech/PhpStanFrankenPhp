<?php

declare(strict_types=1);

namespace NowoTech\PhpStanFrankenPhp\Tests\Integration;

use PHPUnit\Framework\TestCase;

final class RulesetWiringTest extends TestCase
{
    /**
     * @return iterable<string, array{0: string}>
     */
    public static function neonFilesProvider(): iterable
    {
        yield 'extension' => ['extension.neon'];
        yield 'classic' => ['ruleset-classic.neon'];
        yield 'worker' => ['ruleset-worker.neon'];
        yield 'worker-strict' => ['ruleset-worker-strict.neon'];
        yield 'hardening' => ['ruleset-hardening.neon'];
        yield 'all' => ['rules.neon'];
        yield 'rules/classic' => ['rules/classic.neon'];
        yield 'rules/worker' => ['rules/worker.neon'];
        yield 'rules/hardening' => ['rules/hardening.neon'];
    }

    /**
     * @dataProvider neonFilesProvider
     */
    public function testNeonFileExistsAndIsReadable(string $relative): void
    {
        $path = \dirname(__DIR__, 2).'/'.$relative;
        self::assertFileExists($path);
        $contents = file_get_contents($path);
        self::assertNotFalse($contents);
        self::assertNotSame('', trim($contents));
    }

    public function testWorkerRulesetRegistersNewHandlers(): void
    {
        $contents = (string) file_get_contents(\dirname(__DIR__, 2).'/rules/worker.neon');
        self::assertStringContainsString('NoRegisterShutdownFunctionRule', $contents);
        self::assertStringContainsString('NoSetErrorExceptionHandlerRule', $contents);
        self::assertStringContainsString('flagRequestSuperglobals', $contents);
    }

    public function testExtensionDeclaresParameterSchema(): void
    {
        $contents = (string) file_get_contents(\dirname(__DIR__, 2).'/extension.neon');
        self::assertStringContainsString('parametersSchema', $contents);
        self::assertStringContainsString('flagRequestSuperglobals', $contents);
    }
}

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
        yield 'worker-no-kernel-reset' => ['ruleset-worker-no-kernel-reset.neon'];
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
        self::assertStringContainsString('NoChdirRule', $contents);
        self::assertStringContainsString('NoSetLocaleRule', $contents);
        self::assertStringContainsString('NoLocaleSetDefaultRule', $contents);
        self::assertStringContainsString('NoDateDefaultTimezoneSetRule', $contents);
        self::assertStringContainsString('NoMbEncodingMutationRule', $contents);
        self::assertStringContainsString('NoErrorReportingMutationRule', $contents);
        self::assertStringContainsString('NoUmaskRule', $contents);
        self::assertStringContainsString('NoMissingResetInterfaceRule', $contents);
        self::assertStringContainsString('flagRequestSuperglobals', $contents);
        self::assertStringContainsString('flagMissingResetInterface', $contents);
    }

    public function testHardeningRulesetRegistersPcntlSignal(): void
    {
        $contents = (string) file_get_contents(\dirname(__DIR__, 2).'/rules/hardening.neon');
        self::assertStringContainsString('NoPcntlSignalRule', $contents);
        self::assertStringContainsString('NoPosixProcessControlRule', $contents);
    }

    public function testExtensionDeclaresParameterSchema(): void
    {
        $contents = (string) file_get_contents(\dirname(__DIR__, 2).'/extension.neon');
        self::assertStringContainsString('parametersSchema', $contents);
        self::assertStringContainsString('flagRequestSuperglobals', $contents);
        self::assertStringContainsString('flagMissingResetInterface', $contents);
    }

    public function testWorkerNoKernelResetRulesetEnablesMissingResetInterface(): void
    {
        $contents = (string) file_get_contents(\dirname(__DIR__, 2).'/ruleset-worker-no-kernel-reset.neon');
        self::assertStringContainsString('flagMissingResetInterface: true', $contents);
    }
}

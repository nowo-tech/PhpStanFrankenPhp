<?php

declare(strict_types=1);

namespace NowoTech\PhpStanFrankenPhp\Tests\Unit;

use NowoTech\PhpStanFrankenPhp\Rule\Classic\NoFastCgiFinishRequestRule;
use NowoTech\PhpStanFrankenPhp\Rule\Classic\NoIgnoreUserAbortRule;
use NowoTech\PhpStanFrankenPhp\Rule\Classic\NoPutenvRule;
use NowoTech\PhpStanFrankenPhp\Rule\Classic\NoUnlimitedIoTimeoutRule;
use NowoTech\PhpStanFrankenPhp\Rule\Hardening\NoBlockingSleepRule;
use NowoTech\PhpStanFrankenPhp\Rule\Hardening\NoPcntlForkRule;
use NowoTech\PhpStanFrankenPhp\Rule\Hardening\NoPcntlSignalRule;
use NowoTech\PhpStanFrankenPhp\Rule\Hardening\NoRegisterTickFunctionRule;
use NowoTech\PhpStanFrankenPhp\Rule\Hardening\NoUnlimitedExecutionTimeRule;
use NowoTech\PhpStanFrankenPhp\Rule\Hardening\NoUnlimitedMemoryRule;
use NowoTech\PhpStanFrankenPhp\Rule\Worker\NoChdirRule;
use NowoTech\PhpStanFrankenPhp\Rule\Worker\NoDateDefaultTimezoneSetRule;
use NowoTech\PhpStanFrankenPhp\Rule\Worker\NoErrorReportingMutationRule;
use NowoTech\PhpStanFrankenPhp\Rule\Worker\NoGlobalStateWriteRule;
use NowoTech\PhpStanFrankenPhp\Rule\Worker\NoLocaleSetDefaultRule;
use NowoTech\PhpStanFrankenPhp\Rule\Worker\NoMbEncodingMutationRule;
use NowoTech\PhpStanFrankenPhp\Rule\Worker\NoMutableStaticPropertyRule;
use NowoTech\PhpStanFrankenPhp\Rule\Worker\NoNativeSessionApiRule;
use NowoTech\PhpStanFrankenPhp\Rule\Worker\NoPersistentIniSetRule;
use NowoTech\PhpStanFrankenPhp\Rule\Worker\NoRegisterShutdownFunctionRule;
use NowoTech\PhpStanFrankenPhp\Rule\Worker\NoSetErrorExceptionHandlerRule;
use NowoTech\PhpStanFrankenPhp\Rule\Worker\NoSetLocaleRule;
use NowoTech\PhpStanFrankenPhp\Rule\Worker\NoStaticLocalVariableRule;
use NowoTech\PhpStanFrankenPhp\Rule\Worker\NoSuperglobalAccessRule;
use NowoTech\PhpStanFrankenPhp\Rule\Worker\NoUmaskRule;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Expr\ArrayDimFetch;
use PhpParser\Node\Expr\ArrayItem;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\ConstFetch;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name;
use PhpParser\Node\Scalar\LNumber;
use PhpParser\Node\Scalar\String_;
use PhpParser\Node\Stmt\Nop;
use PHPStan\Analyser\Scope;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Exercises early-return / edge branches that RuleTestCase never hits
 * because PHPStan only feeds matching node types.
 */
final class DirectProcessNodeCoverageTest extends TestCase
{
    private MockObject $scope;

    protected function setUp(): void
    {
        $this->scope = $this->createMock(Scope::class);
    }

    public function testClassicEarlyReturns(): void
    {
        $wrong = new FuncCall(new Name('strlen'), []);
        self::assertSame([], (new NoFastCgiFinishRequestRule())->processNode($wrong, $this->scope));
        self::assertSame([], (new NoPutenvRule())->processNode($wrong, $this->scope));
        self::assertSame([], (new NoIgnoreUserAbortRule())->processNode($wrong, $this->scope));
        self::assertSame([], (new NoIgnoreUserAbortRule())->processNode(new Nop(), $this->scope));

        $timeout = new NoUnlimitedIoTimeoutRule();
        self::assertSame([], $timeout->processNode(new Nop(), $this->scope));

        $exprName = new MethodCall(new Variable('p'), new Variable('m'), []);
        self::assertSame([], $timeout->processNode($exprName, $this->scope));

        $otherMethod = new MethodCall(new Variable('p'), new Identifier('run'), []);
        self::assertSame([], $timeout->processNode($otherMethod, $this->scope));

        $noArgs = new MethodCall(new Variable('p'), new Identifier('setTimeout'), []);
        self::assertSame([], $timeout->processNode($noArgs, $this->scope));

        $finite = new MethodCall(new Variable('p'), new Identifier('setTimeout'), [
            new Arg(new LNumber(10)),
        ]);
        self::assertSame([], $timeout->processNode($finite, $this->scope));

        $notCurl = new FuncCall(new Name('strlen'), []);
        self::assertSame([], $timeout->processNode($notCurl, $this->scope));

        $curlShort = new FuncCall(new Name('curl_setopt'), [new Arg(new Variable('ch'))]);
        self::assertSame([], $timeout->processNode($curlShort, $this->scope));

        $curlArrayBad = new FuncCall(new Name('curl_setopt_array'), [
            new Arg(new Variable('ch')),
            new Arg(new String_('nope')),
        ]);
        self::assertSame([], $timeout->processNode($curlArrayBad, $this->scope));

        $curlArrayEmptyItem = new FuncCall(new Name('curl_setopt_array'), [
            new Arg(new Variable('ch')),
            new Arg(new Array_([null])),
        ]);
        self::assertSame([], $timeout->processNode($curlArrayEmptyItem, $this->scope));

        $curlFinite = new FuncCall(new Name('curl_setopt'), [
            new Arg(new Variable('ch')),
            new Arg(new ConstFetch(new Name('CURLOPT_TIMEOUT'))),
            new Arg(new LNumber(15)),
        ]);
        self::assertSame([], $timeout->processNode($curlFinite, $this->scope));

        $curlNonOption = new FuncCall(new Name('curl_setopt'), [
            new Arg(new Variable('ch')),
            new Arg(new String_('not-a-const')),
            new Arg(new LNumber(0)),
        ]);
        self::assertSame([], $timeout->processNode($curlNonOption, $this->scope));

        $curlArrayNonTimeout = new FuncCall(new Name('curl_setopt_array'), [
            new Arg(new Variable('ch')),
            new Arg(new Array_([
                new ArrayItem(new LNumber(1), new ConstFetch(new Name('CURLOPT_RETURNTRANSFER'))),
            ])),
        ]);
        self::assertSame([], $timeout->processNode($curlArrayNonTimeout, $this->scope));
    }

    public function testWorkerAndHardeningEarlyReturns(): void
    {
        $wrong = new FuncCall(new Name('strlen'), []);
        self::assertSame([], (new NoNativeSessionApiRule())->processNode($wrong, $this->scope));
        self::assertSame([], (new NoPersistentIniSetRule())->processNode($wrong, $this->scope));
        self::assertSame([], (new NoRegisterShutdownFunctionRule())->processNode($wrong, $this->scope));
        self::assertSame([], (new NoSetErrorExceptionHandlerRule())->processNode($wrong, $this->scope));
        self::assertSame([], (new NoChdirRule())->processNode($wrong, $this->scope));
        self::assertSame([], (new NoSetLocaleRule())->processNode($wrong, $this->scope));
        self::assertSame([], (new NoLocaleSetDefaultRule())->processNode($wrong, $this->scope));
        self::assertSame([], (new NoDateDefaultTimezoneSetRule())->processNode($wrong, $this->scope));
        self::assertSame([], (new NoMbEncodingMutationRule())->processNode($wrong, $this->scope));
        self::assertSame([], (new NoErrorReportingMutationRule())->processNode($wrong, $this->scope));
        self::assertSame([], (new NoUmaskRule())->processNode($wrong, $this->scope));
        self::assertSame([], (new NoBlockingSleepRule())->processNode($wrong, $this->scope));
        self::assertSame([], (new NoPcntlForkRule())->processNode($wrong, $this->scope));
        self::assertSame([], (new NoPcntlSignalRule())->processNode($wrong, $this->scope));
        self::assertSame([], (new NoRegisterTickFunctionRule())->processNode($wrong, $this->scope));
        self::assertSame([], (new NoUnlimitedExecutionTimeRule())->processNode($wrong, $this->scope));
        self::assertSame([], (new NoUnlimitedMemoryRule())->processNode($wrong, $this->scope));

        $mbRead = new FuncCall(new Name('mb_internal_encoding'), []);
        self::assertSame([], (new NoMbEncodingMutationRule())->processNode($mbRead, $this->scope));

        $errorReportingRead = new FuncCall(new Name('error_reporting'), []);
        self::assertSame([], (new NoErrorReportingMutationRule())->processNode($errorReportingRead, $this->scope));

        $umaskRead = new FuncCall(new Name('umask'), []);
        self::assertSame([], (new NoUmaskRule())->processNode($umaskRead, $this->scope));

        $setLocaleQueryInt = new FuncCall(new Name('setlocale'), [
            new Arg(new ConstFetch(new Name('LC_ALL'))),
            new Arg(new LNumber(0)),
        ]);
        self::assertSame([], (new NoSetLocaleRule())->processNode($setLocaleQueryInt, $this->scope));

        $setLocaleQueryString = new FuncCall(new Name('setlocale'), [
            new Arg(new ConstFetch(new Name('LC_ALL'))),
            new Arg(new String_('0')),
        ]);
        self::assertSame([], (new NoSetLocaleRule())->processNode($setLocaleQueryString, $this->scope));

        $setTimeFinite = new FuncCall(new Name('set_time_limit'), [new Arg(new LNumber(30))]);
        self::assertSame([], (new NoUnlimitedExecutionTimeRule())->processNode($setTimeFinite, $this->scope));

        $iniNoArgs = new FuncCall(new Name('ini_set'), []);
        self::assertSame([], (new NoUnlimitedMemoryRule())->processNode($iniNoArgs, $this->scope));

        $iniOther = new FuncCall(new Name('ini_set'), [
            new Arg(new String_('user_agent')),
            new Arg(new String_('x')),
        ]);
        self::assertSame([], (new NoUnlimitedMemoryRule())->processNode($iniOther, $this->scope));

        $iniNegLiteral = new FuncCall(new Name('ini_set'), [
            new Arg(new String_('memory_limit')),
            new Arg(new LNumber(-1)),
        ]);
        self::assertNotSame([], (new NoUnlimitedMemoryRule())->processNode($iniNegLiteral, $this->scope));

        self::assertSame([], (new NoStaticLocalVariableRule())->processNode(new Nop(), $this->scope));
        self::assertSame([], (new NoMutableStaticPropertyRule())->processNode(new Nop(), $this->scope));

        $assignOther = new Assign(new Variable('x'), new LNumber(1));
        self::assertSame([], (new NoGlobalStateWriteRule())->processNode($assignOther, $this->scope));
        self::assertSame([], (new NoMutableStaticPropertyRule())->processNode($assignOther, $this->scope));

        $varExpr = new Variable(new Variable('dyn'));
        self::assertSame([], (new NoSuperglobalAccessRule())->processNode($varExpr, $this->scope));
        self::assertSame([], (new NoSuperglobalAccessRule())->processNode(new Variable('foo'), $this->scope));
        self::assertSame([], (new NoSuperglobalAccessRule())->processNode(new Nop(), $this->scope));

        // Force false branch of isGlobalsWrite recursion end
        $nested = new Assign(
            new ArrayDimFetch(new Variable('notGlobals'), new String_('a')),
            new LNumber(1)
        );
        self::assertSame([], (new NoGlobalStateWriteRule())->processNode($nested, $this->scope));
    }
}

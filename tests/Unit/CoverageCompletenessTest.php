<?php

declare(strict_types=1);

namespace NowoTech\PhpStanFrankenPhp\Tests\Unit;

use NowoTech\PhpStanFrankenPhp\Rule\Classic\NoExitOrDieRule;
use NowoTech\PhpStanFrankenPhp\Rule\Classic\NoFastCgiFinishRequestRule;
use NowoTech\PhpStanFrankenPhp\Rule\Classic\NoIgnoreUserAbortRule;
use NowoTech\PhpStanFrankenPhp\Rule\Classic\NoPutenvRule;
use NowoTech\PhpStanFrankenPhp\Rule\Classic\NoUnlimitedIoTimeoutRule;
use NowoTech\PhpStanFrankenPhp\Rule\Hardening\NoBlockingSleepRule;
use NowoTech\PhpStanFrankenPhp\Rule\Hardening\NoPcntlForkRule;
use NowoTech\PhpStanFrankenPhp\Rule\Hardening\NoRegisterTickFunctionRule;
use NowoTech\PhpStanFrankenPhp\Rule\Hardening\NoUnlimitedExecutionTimeRule;
use NowoTech\PhpStanFrankenPhp\Rule\Hardening\NoUnlimitedMemoryRule;
use NowoTech\PhpStanFrankenPhp\Rule\Worker\NoGlobalStateWriteRule;
use NowoTech\PhpStanFrankenPhp\Rule\Worker\NoMutableStaticPropertyRule;
use NowoTech\PhpStanFrankenPhp\Rule\Worker\NoNativeSessionApiRule;
use NowoTech\PhpStanFrankenPhp\Rule\Worker\NoPersistentIniSetRule;
use NowoTech\PhpStanFrankenPhp\Rule\Worker\NoRegisterShutdownFunctionRule;
use NowoTech\PhpStanFrankenPhp\Rule\Worker\NoSetErrorExceptionHandlerRule;
use NowoTech\PhpStanFrankenPhp\Rule\Worker\NoSingletonGetInstanceRule;
use NowoTech\PhpStanFrankenPhp\Rule\Worker\NoStaticLocalVariableRule;
use NowoTech\PhpStanFrankenPhp\Rule\Worker\NoSuperglobalAccessRule;
use NowoTech\PhpStanFrankenPhp\Support\NodeHelper;
use PhpParser\Node\Expr\ConstFetch;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Name;
use PhpParser\Node\Scalar\DNumber;
use PhpParser\Node\Scalar\LNumber;
use PhpParser\Node\Scalar\String_;
use PHPStan\Analyser\Scope;
use PHPStan\Rules\Rule;
use PHPUnit\Framework\TestCase;

final class CoverageCompletenessTest extends TestCase
{
    /**
     * @return iterable<string, array{0: object}>
     */
    public static function rulesProvider(): iterable
    {
        yield 'exit' => [new NoExitOrDieRule()];
        yield 'fastcgi' => [new NoFastCgiFinishRequestRule()];
        yield 'putenv' => [new NoPutenvRule()];
        yield 'ignore' => [new NoIgnoreUserAbortRule()];
        yield 'timeout' => [new NoUnlimitedIoTimeoutRule()];
        yield 'staticProp' => [new NoMutableStaticPropertyRule()];
        yield 'staticLocal' => [new NoStaticLocalVariableRule()];
        yield 'global' => [new NoGlobalStateWriteRule()];
        yield 'super' => [new NoSuperglobalAccessRule(false)];
        yield 'session' => [new NoNativeSessionApiRule()];
        yield 'ini' => [new NoPersistentIniSetRule()];
        yield 'singleton' => [new NoSingletonGetInstanceRule()];
        yield 'shutdown' => [new NoRegisterShutdownFunctionRule()];
        yield 'handlers' => [new NoSetErrorExceptionHandlerRule()];
        yield 'time' => [new NoUnlimitedExecutionTimeRule()];
        yield 'memory' => [new NoUnlimitedMemoryRule()];
        yield 'fork' => [new NoPcntlForkRule()];
        yield 'sleep' => [new NoBlockingSleepRule()];
        yield 'tick' => [new NoRegisterTickFunctionRule()];
    }

    /**
     * @dataProvider rulesProvider
     */
    public function testGetNodeTypeIsString(object $rule): void
    {
        self::assertInstanceOf(Rule::class, $rule);
        self::assertNotSame('', $rule->getNodeType());
    }

    public function testNodeHelperFunctionNamesAndLiterals(): void
    {
        $named = new FuncCall(new Name('putenv'), []);
        self::assertTrue(NodeHelper::isFunctionNamedExactly($named, 'putenv'));
        self::assertTrue(NodeHelper::isFunctionNamed($named, ['PUTENV']));
        self::assertFalse(NodeHelper::isFunctionNamedExactly($named, 'getenv'));

        $dynamic = new FuncCall(new Variable('fn'), []);
        self::assertFalse(NodeHelper::isFunctionNamed($dynamic, ['putenv']));

        self::assertNull(NodeHelper::firstArgExpr($named));
        self::assertNull(NodeHelper::argExpr($named, 0));

        self::assertTrue(NodeHelper::isZeroLikeLiteral(new LNumber(0)));
        self::assertTrue(NodeHelper::isZeroLikeLiteral(new DNumber(0.0)));
        self::assertTrue(NodeHelper::isZeroLikeLiteral(new String_('0')));
        self::assertTrue(NodeHelper::isZeroLikeLiteral(new ConstFetch(new Name('null'))));
        self::assertFalse(NodeHelper::isZeroLikeLiteral(new LNumber(1)));
        self::assertFalse(NodeHelper::isZeroLikeLiteral(new String_('x')));
        self::assertFalse(NodeHelper::isZeroLikeLiteral(new Variable('n')));

        $scope = $this->createMock(Scope::class);
        $scope->method('isInClass')->willReturn(true);
        self::assertTrue(NodeHelper::isInClass($scope));
    }
}

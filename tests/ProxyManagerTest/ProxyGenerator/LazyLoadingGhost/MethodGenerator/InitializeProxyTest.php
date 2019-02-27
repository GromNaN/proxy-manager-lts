<?php

declare(strict_types=1);

namespace ProxyManagerTest\ProxyGenerator\LazyLoadingGhost\MethodGenerator;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use ProxyManager\ProxyGenerator\LazyLoadingGhost\MethodGenerator\InitializeProxy;
use Zend\Code\Generator\MethodGenerator;
use Zend\Code\Generator\PropertyGenerator;

/**
 * Tests for {@see \ProxyManager\ProxyGenerator\LazyLoadingGhost\MethodGenerator\InitializeProxy}
 *
 * @group Coverage
 */
class InitializeProxyTest extends TestCase
{
    /**
     * @covers \ProxyManager\ProxyGenerator\LazyLoadingGhost\MethodGenerator\InitializeProxy::__construct
     */
    public function testBodyStructure() : void
    {
        /** @var PropertyGenerator|MockObject $initializer */
        $initializer = $this->createMock(PropertyGenerator::class);
        /** @var MethodGenerator|MockObject $initCall */
        $initCall = $this->createMock(MethodGenerator::class);

        $initializer->expects(self::any())->method('getName')->will(self::returnValue('foo'));
        $initCall->expects(self::any())->method('getName')->will(self::returnValue('bar'));

        $initializeProxy = new InitializeProxy($initializer, $initCall);

        self::assertSame('initializeProxy', $initializeProxy->getName());
        self::assertCount(0, $initializeProxy->getParameters());
        self::assertSame(
            'return $this->foo && $this->bar(\'initializeProxy\', []);',
            $initializeProxy->getBody()
        );
        self::assertStringMatchesFormat('%A : bool%A', $initializeProxy->generate(), 'Return type hint is boolean');
    }
}

<?php

declare(strict_types=1);

namespace ProxyManagerTest\ProxyGenerator\LazyLoadingValueHolder\MethodGenerator;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use ProxyManager\ProxyGenerator\LazyLoadingValueHolder\MethodGenerator\MagicSet;
use ProxyManager\ProxyGenerator\PropertyGenerator\PublicPropertiesMap;
use ProxyManagerTestAsset\ClassWithMagicMethods;
use ProxyManagerTestAsset\EmptyClass;
use ReflectionClass;
use Zend\Code\Generator\PropertyGenerator;

/**
 * Tests for {@see \ProxyManager\ProxyGenerator\LazyLoadingValueHolder\MethodGenerator\MagicSet}
 *
 * @group Coverage
 * @covers \ProxyManager\ProxyGenerator\LazyLoadingValueHolder\MethodGenerator\MagicSet
 */
class MagicSetTest extends TestCase
{
    public function testBodyStructure() : void
    {
        $reflection = new ReflectionClass(EmptyClass::class);
        /** @var PropertyGenerator|MockObject $initializer */
        $initializer = $this->createMock(PropertyGenerator::class);
        /** @var PropertyGenerator|MockObject $valueHolder */
        $valueHolder = $this->createMock(PropertyGenerator::class);
        /** @var PublicPropertiesMap|MockObject $publicProperties */
        $publicProperties = $this
            ->getMockBuilder(PublicPropertiesMap::class)
            ->disableOriginalConstructor()
            ->getMock();

        $initializer->expects(self::any())->method('getName')->will(self::returnValue('foo'));
        $valueHolder->expects(self::any())->method('getName')->will(self::returnValue('bar'));
        $publicProperties->expects(self::any())->method('isEmpty')->will(self::returnValue(false));
        $publicProperties->expects(self::any())->method('getName')->will(self::returnValue('bar'));

        $magicSet = new MagicSet($reflection, $initializer, $valueHolder, $publicProperties);

        self::assertSame('__set', $magicSet->getName());
        self::assertCount(2, $magicSet->getParameters());
        self::assertStringMatchesFormat(
            '$this->foo && $this->foo->__invoke($this->bar, $this, '
            . "'__set', array('name' => \$name, 'value' => \$value), \$this->foo);\n\n"
            . "if (isset(self::\$bar[\$name])) {\n    return (\$this->bar->\$name = \$value);\n}"
            . '%areturn %s;',
            $magicSet->getBody()
        );
    }

    /**
     * @group 344
     */
    public function testBodyStructureWithPreExistingMagicMethod() : void
    {
        $reflection = new ReflectionClass(ClassWithMagicMethods::class);
        /** @var PropertyGenerator|MockObject $initializer */
        $initializer = $this->createMock(PropertyGenerator::class);
        /** @var PropertyGenerator|MockObject $valueHolder */
        $valueHolder = $this->createMock(PropertyGenerator::class);
        /** @var PublicPropertiesMap|MockObject $publicProperties */
        $publicProperties = $this
            ->getMockBuilder(PublicPropertiesMap::class)
            ->disableOriginalConstructor()
            ->getMock();

        $initializer->expects(self::any())->method('getName')->will(self::returnValue('foo'));
        $valueHolder->expects(self::any())->method('getName')->will(self::returnValue('bar'));
        $publicProperties->expects(self::any())->method('isEmpty')->will(self::returnValue(false));
        $publicProperties->expects(self::any())->method('getName')->will(self::returnValue('bar'));

        $magicSet = new MagicSet($reflection, $initializer, $valueHolder, $publicProperties);

        self::assertSame('__set', $magicSet->getName());
        self::assertCount(2, $magicSet->getParameters());
        self::assertStringMatchesFormat(
            '$this->foo && $this->foo->__invoke($this->bar, $this, '
            . "'__set', array('name' => \$name, 'value' => \$value), \$this->foo);\n\n"
            . "if (isset(self::\$bar[\$name])) {\n    return (\$this->bar->\$name = \$value);\n}\n\n"
            . 'return $this->bar->__set($name, $value);',
            $magicSet->getBody(),
            'Execution is deferred to pre-existing `__set` if the property is not accessible'
        );
    }
}

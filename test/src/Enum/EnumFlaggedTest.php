<?php

namespace RealejoTest\Enum;

use PHPUnit\Framework\TestCase;

class EnumFlaggedTest extends TestCase
{

    public function testGetName()
    {
        $enum = new EnumFlaggedConcreteEmpty();
        $this->assertEquals([], $enum->getNames());
        $this->assertNull($enum->getName());

        $enum = new EnumFlaggedConcrete();
        $this->assertEquals([
            1 => 'x',
            2 => 'w',
            4 => 'r'
        ], $enum->getNames());
        $this->assertNull($enum->getName());
        $this->assertNull($enum->getName('Z'));
        $this->assertNull($enum->getName(8));
        $this->assertEquals('x', $enum->getName(1));
        $this->assertEquals('w', $enum->getName(2));
        $this->assertEquals('r', $enum->getName(4));
        $this->assertNull($enum->getName('1'));
        $this->assertNull($enum->getName('2'));
        $this->assertNull($enum->getName('4'));

        $this->assertEquals('x/w', $enum->getName(1 + 2));
        $this->assertEquals('xw', $enum->getName(1 + 2, ''));
        $this->assertEquals([
            1 => 'x',
            2 => 'w',
        ], $enum->getName(1 + 2, false));

    }

    public function testGetNameStatic()
    {
        $this->assertEquals([], EnumFlaggedConcreteEmpty::getNames());
        $this->assertNull(EnumFlaggedConcreteEmpty::getName());

        $this->assertEquals([
            1 => 'x',
            2 => 'w',
            4 => 'r'
        ], EnumFlaggedConcrete::getNames());
        $this->assertNull(EnumFlaggedConcrete::getName());
        $this->assertNull(EnumFlaggedConcrete::getName('Z'));
        $this->assertNull(EnumFlaggedConcrete::getName(8));
        $this->assertEquals('x', EnumFlaggedConcrete::getName(1));
        $this->assertEquals('w', EnumFlaggedConcrete::getName(2));
        $this->assertEquals('r', EnumFlaggedConcrete::getName(4));
        $this->assertNull(EnumFlaggedConcrete::getName('1'));
        $this->assertNull(EnumFlaggedConcrete::getName('2'));
        $this->assertNull(EnumFlaggedConcrete::getName('4'));

        $this->assertEquals('x/w', EnumFlaggedConcrete::getName(1 + 2));
        $this->assertEquals('xw', EnumFlaggedConcrete::getName(1 + 2, ''));
        $this->assertEquals([
            1 => 'x',
            2 => 'w',
        ], EnumFlaggedConcrete::getName(1 + 2, false));

    }

    public function testGetDescription()
    {
        $enum = new EnumFlaggedConcreteEmpty();
        $this->assertEquals([], $enum->getDescriptions());
        $this->assertNull($enum->getDescription());

        $enum = new EnumFlaggedConcrete();
        $this->assertEquals([
            1 => 'execute',
            2 => 'w',
            4 => 'r'
        ], $enum->getDescriptions());
        $this->assertNull($enum->getDescription());
        $this->assertNull($enum->getDescription('Z'));
        $this->assertNull($enum->getDescription(8));
        $this->assertEquals('execute', $enum->getDescription(1));
        $this->assertEquals('w', $enum->getDescription(2));
        $this->assertEquals('r', $enum->getDescription(4));
        $this->assertNull($enum->getDescription('1'));
        $this->assertNull($enum->getDescription('2'));
        $this->assertNull($enum->getDescription('4'));

        $this->assertEquals('execute/w', $enum->getDescription(1 + 2));
        $this->assertEquals('executew', $enum->getDescription(1 + 2, ''));
        $this->assertEquals([
            1 => 'execute',
            2 => 'w',
        ], $enum->getDescription(1 + 2, false));

    }

    public function testGetDescriptionStatic()
    {
        $this->assertEquals([], EnumFlaggedConcreteEmpty::getDescriptions());
        $this->assertNull(EnumFlaggedConcreteEmpty::getDescription());

        $this->assertEquals([
            1 => 'execute',
            2 => 'w',
            4 => 'r'
        ], EnumFlaggedConcrete::getDescriptions());
        $this->assertNull(EnumFlaggedConcrete::getDescription());
        $this->assertNull(EnumFlaggedConcrete::getDescription('Z'));
        $this->assertNull(EnumFlaggedConcrete::getDescription(8));
        $this->assertEquals('execute', EnumFlaggedConcrete::getDescription(1));
        $this->assertEquals('w', EnumFlaggedConcrete::getDescription(2));
        $this->assertEquals('r', EnumFlaggedConcrete::getDescription(4));
        $this->assertNull(EnumFlaggedConcrete::getDescription('1'));
        $this->assertNull(EnumFlaggedConcrete::getDescription('2'));
        $this->assertNull(EnumFlaggedConcrete::getDescription('4'));

        $this->assertEquals('execute/w', EnumFlaggedConcrete::getDescription(1 + 2));
        $this->assertEquals('executew', EnumFlaggedConcrete::getDescription(1 + 2, ''));
        $this->assertEquals([
            1 => 'execute',
            2 => 'w',
        ], EnumFlaggedConcrete::getDescription(1 + 2, false));

    }

    public function testIsValid()
    {
        $enum = new EnumFlaggedConcreteEmpty();
        $this->assertTrue($enum->isValid(0));
        $this->assertFalse($enum->isValid(null));
        $this->assertFalse($enum->isValid(''));
        $this->assertFalse($enum->isValid(1));

        $enum = new EnumFlaggedConcrete();
        $this->assertTrue($enum->isValid(0));
        $this->assertFalse($enum->isValid(null));
        $this->assertFalse($enum->isValid(''));
        $this->assertFalse($enum->isValid(false));
        $this->assertFalse($enum->isValid(true));

        $this->assertTrue($enum->isValid(1));
        $this->assertFalse($enum->isValid('1'));
        $this->assertFalse($enum->isValid(1.0));

        $this->assertTrue($enum->isValid(2));
        $this->assertFalse($enum->isValid('2'));
        $this->assertFalse($enum->isValid(2.0));

        $this->assertTrue($enum->isValid(3));
        $this->assertFalse($enum->isValid('3'));
        $this->assertFalse($enum->isValid(3.0));

        $this->assertTrue($enum->isValid(8));
        $this->assertFalse($enum->isValid('8'));
        $this->assertFalse($enum->isValid(8.0));
    }

    public function testIsValidStatic()
    {
        $this->assertTrue(EnumFlaggedConcreteEmpty::isValid(0));
        $this->assertFalse(EnumFlaggedConcreteEmpty::isValid(null));
        $this->assertFalse(EnumFlaggedConcreteEmpty::isValid(''));
        $this->assertFalse(EnumFlaggedConcreteEmpty::isValid(1));

        $this->assertTrue(EnumFlaggedConcrete::isValid(0));
        $this->assertFalse(EnumFlaggedConcrete::isValid(null));
        $this->assertFalse(EnumFlaggedConcrete::isValid(''));
        $this->assertFalse(EnumFlaggedConcrete::isValid(false));
        $this->assertFalse(EnumFlaggedConcrete::isValid(true));

        $this->assertTrue(EnumFlaggedConcrete::isValid(1));
        $this->assertFalse(EnumFlaggedConcrete::isValid('1'));
        $this->assertFalse(EnumFlaggedConcrete::isValid(1.0));

        $this->assertTrue(EnumFlaggedConcrete::isValid(2));
        $this->assertFalse(EnumFlaggedConcrete::isValid('2'));
        $this->assertFalse(EnumFlaggedConcrete::isValid(2.0));

        $this->assertTrue(EnumFlaggedConcrete::isValid(3));
        $this->assertFalse(EnumFlaggedConcrete::isValid('3'));
        $this->assertFalse(EnumFlaggedConcrete::isValid(3.0));

        $this->assertTrue(EnumFlaggedConcrete::isValid(8));
        $this->assertFalse(EnumFlaggedConcrete::isValid('8'));
        $this->assertFalse(EnumFlaggedConcrete::isValid(8.0));
    }

    public function testGetValue()
    {
        $enum = new EnumFlaggedConcreteEmpty();
        $this->assertEquals(0, $enum->getValue());

        $enum = new EnumFlaggedConcrete();
        $this->assertEquals(0, $enum->getValue());
        $this->assertNull($enum->getName());
        $this->assertNull($enum->getDescription());

        $enum = new EnumFlaggedConcrete(EnumFlaggedConcrete::EXECUTE);
        $this->assertEquals(EnumFlaggedConcrete::EXECUTE, $enum->getValue());
        $this->assertEquals('x', $enum->getName($enum->getValue()));
        $this->assertEquals('execute', $enum->getDescription($enum->getValue()));
        $this->assertEquals('x', $enum->getName());
        $this->assertEquals('execute', $enum->getDescription());

        $enum = new EnumFlaggedConcrete(EnumFlaggedConcrete::WRITE);
        $this->assertEquals(EnumFlaggedConcrete::WRITE, $enum->getValue());
        $this->assertEquals('w', $enum->getName($enum->getValue()));
        $this->assertEquals('w', $enum->getDescription($enum->getValue()));
        $this->assertEquals('w', $enum->getName());
        $this->assertEquals('w', $enum->getDescription());

        $enum = new EnumFlaggedConcrete(EnumFlaggedConcrete::READ);
        $this->assertEquals(EnumFlaggedConcrete::READ, $enum->getValue());
        $this->assertEquals('r', $enum->getName($enum->getValue()));
        $this->assertEquals('r', $enum->getDescription($enum->getValue()));
        $this->assertEquals('r', $enum->getName());
        $this->assertEquals('r', $enum->getDescription());

        $enum = new EnumFlaggedConcrete(EnumFlaggedConcrete::READ + EnumFlaggedConcrete::WRITE);
        $this->assertEquals(6, $enum->getValue());
        $this->assertEquals('w/r', $enum->getName($enum->getValue()));
        $this->assertEquals('w/r', $enum->getDescription($enum->getValue()));
        $this->assertEquals('w/r', $enum->getName());
        $this->assertEquals('w/r', $enum->getDescription());
    }

    public function testIsHas()
    {
        $enum = new EnumFlaggedConcreteEmpty();
        $this->assertEquals(0, $enum->getValue());
        $this->assertTrue($enum->is(0));
        $this->assertFalse($enum->is(1));
        $this->assertTrue($enum->has(0));
        $this->assertFalse($enum->has(1));

        $enum = new EnumFlaggedConcrete();
        $this->assertEquals(0, $enum->getValue());
        $this->assertTrue($enum->is(0));
        $this->assertFalse($enum->is(1));
        $this->assertTrue($enum->has(0));
        $this->assertFalse($enum->has(1));

        $enum = new EnumFlaggedConcrete(EnumFlaggedConcrete::EXECUTE);
        $this->assertEquals(EnumFlaggedConcrete::EXECUTE, $enum->getValue());
        $this->assertFalse($enum->is(0));
        $this->assertTrue($enum->is(1));
        $this->assertFalse($enum->is('1'));
        $this->assertFalse($enum->is(2));
        $this->assertFalse($enum->is(4));

        $this->assertTrue($enum->has(0));
        $this->assertTrue($enum->has(1));
        $this->assertFalse($enum->has('1'));
        $this->assertFalse($enum->has(2));
        $this->assertFalse($enum->has(4));

        $this->assertFalse($enum->has(3));
        $this->assertFalse($enum->has(5));
        $this->assertFalse($enum->has(7));

        $enum = new EnumFlaggedConcrete(EnumFlaggedConcrete::READ+EnumFlaggedConcrete::WRITE);
        $this->assertEquals(EnumFlaggedConcrete::READ+EnumFlaggedConcrete::WRITE, $enum->getValue());
        $this->assertFalse($enum->is(0));
        $this->assertTrue($enum->is(6));
        $this->assertFalse($enum->is('6'));
        $this->assertFalse($enum->is(6.00));

        $this->assertTrue($enum->has(0));
        $this->assertFalse($enum->has(1));
        $this->assertFalse($enum->has('1'));
        $this->assertTrue($enum->has(2));
        $this->assertTrue($enum->has(4));
        $this->assertFalse($enum->has('2'));
        $this->assertFalse($enum->has('4'));

        $this->assertFalse($enum->has(3));
        $this->assertFalse($enum->has(5));
        $this->assertFalse($enum->has(7));
        $this->assertFalse($enum->has(8));
    }
}

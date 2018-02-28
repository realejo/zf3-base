<?php

namespace RealejoTest\Enum;

use PHPUnit\Framework\TestCase;

class EnumTest extends TestCase
{

    public function testGetNames()
    {
        $enum = new EnumConcreteEmpty();
        $this->assertEquals([], $enum->getNames());
        $this->assertNull($enum->getName());
        $this->assertNull($enum->getValueName());

        $enum = new EnumConcrete();
        $this->assertNull($enum->getValue());
        $this->assertEquals([
            'S' => 'string1',
            'X' => 'string2',
            666 => 'numeric1',
            999 => 'numeric2',
        ], $enum->getNames());
        $this->assertNull($enum->getName());
        $this->assertNull($enum->getName('Z'));
        $this->assertNull($enum->getName(123));
        $this->assertEquals('string1', $enum->getName('S'));
        $this->assertEquals('string2', $enum->getName('X'));
        $this->assertEquals('numeric1', $enum->getName(666));
        $this->assertNull($enum->getName('666'));
        $this->assertEquals('numeric2', $enum->getName(999));
        $this->assertNull($enum->getName('999'));

        $this->assertNull($enum->getValueName());
        $this->assertNull($enum->getValueName('Z'));
        $this->assertNull($enum->getValueName(123));
        $this->assertEquals('string1', $enum->getValueName('S'));
        $this->assertEquals('string2', $enum->getValueName('X'));
        $this->assertEquals('numeric1', $enum->getValueName(666));
        $this->assertNull($enum->getValueName('666'));
        $this->assertEquals('numeric2', $enum->getValueName(999));
        $this->assertNull($enum->getValueName('999'));
    }

    public function testGetNamesStatic()
    {
        $this->assertEquals([], EnumConcreteEmpty::getNames());
        $this->assertNull(EnumConcreteEmpty::getName());

        $this->assertEquals([
            'S' => 'string1',
            'X' => 'string2',
            666 => 'numeric1',
            999 => 'numeric2',
        ], EnumConcrete::getNames());
        $this->assertNull(EnumConcrete::getName());
        $this->assertNull(EnumConcrete::getName('Z'));
        $this->assertNull(EnumConcrete::getName(123));
        $this->assertNull(EnumConcrete::getName(false));
        $this->assertEquals('string1', EnumConcrete::getName('S'));
        $this->assertEquals('string2', EnumConcrete::getName('X'));
        $this->assertEquals('numeric1', EnumConcrete::getName(666));
        $this->assertNull(EnumConcrete::getName('666'));
        $this->assertEquals('numeric2', EnumConcrete::getName(999));
        $this->assertNull(EnumConcrete::getName('999'));
    }

    public function testGetDescriptions()
    {
        $enum = new EnumConcreteEmpty();
        $this->assertEquals([], $enum->getDescriptions());
        $this->assertNull($enum->getDescription());
        $this->assertNull($enum->getValueDescription());

        $enum = new EnumConcrete();
        $this->assertNull($enum->getValue());
        $this->assertEquals([
            'S' => 'string1',
            'X' => 'string with description',
            666 => 'numeric1',
            999 => 'numeric with description',
        ], $enum->getDescriptions());
        $this->assertNull($enum->getDescription());
        $this->assertNull($enum->getDescription('Z'));
        $this->assertNull($enum->getDescription(123));
        $this->assertNull($enum->getDescription(false));
        $this->assertEquals('string1', $enum->getDescription('S'));
        $this->assertEquals('string with description', $enum->getDescription('X'));
        $this->assertEquals('numeric1', $enum->getDescription(666));
        $this->assertNull($enum->getDescription('666'));
        $this->assertEquals('numeric with description', $enum->getDescription(999));
        $this->assertNull($enum->getDescription('999'));

        $this->assertNull($enum->getValueDescription());
        $this->assertNull($enum->getValueDescription('Z'));
        $this->assertNull($enum->getValueDescription(123));
        $this->assertNull($enum->getValueDescription(false));
        $this->assertEquals('string1', $enum->getValueDescription('S'));
        $this->assertEquals('string with description', $enum->getValueDescription('X'));
        $this->assertEquals('numeric1', $enum->getValueDescription(666));
        $this->assertNull($enum->getValueDescription('666'));
        $this->assertEquals('numeric with description', $enum->getValueDescription(999));
        $this->assertNull($enum->getValueDescription('999'));
    }

    public function testGetDescriptionsStatic()
    {
        $this->assertEquals([], EnumConcreteEmpty::getDescriptions());
        $this->assertNull(EnumConcreteEmpty::getDescription());

        $this->assertEquals([
            'S' => 'string1',
            'X' => 'string with description',
            666 => 'numeric1',
            999 => 'numeric with description',
        ], EnumConcrete::getDescriptions());
        $this->assertNull(EnumConcrete::getDescription());
        $this->assertNull(EnumConcrete::getDescription('Z'));
        $this->assertNull(EnumConcrete::getDescription(123));
        $this->assertNull(EnumConcrete::getDescription(false));
        $this->assertEquals('string1', EnumConcrete::getDescription('S'));
        $this->assertEquals('string with description', EnumConcrete::getDescription('X'));
        $this->assertEquals('numeric1', EnumConcrete::getDescription(666));
        $this->assertNull(EnumConcrete::getDescription('666'));
        $this->assertEquals('numeric with description', EnumConcrete::getDescription(999));
        $this->assertNull(EnumConcrete::getDescription('999'));
    }

    public function testIsValid()
    {
        $enum = new EnumConcreteEmpty();
        $this->assertFalse($enum->isValid(0));
        $this->assertFalse($enum->isValid(null));
        $this->assertFalse($enum->isValid(''));

        $enum = new EnumConcrete();
        $this->assertFalse($enum->isValid(0));
        $this->assertFalse($enum->isValid(null));
        $this->assertFalse($enum->isValid(''));
        $this->assertFalse($enum->isValid(false));
        $this->assertFalse($enum->isValid(true));

        $this->assertTrue($enum->isValid(666));
        $this->assertFalse($enum->isValid('666'));
        $this->assertFalse($enum->isValid(666.0));

        $this->assertTrue($enum->isValid('X'));
    }

    public function testIsValidStatic()
    {
        $this->assertFalse(EnumConcreteEmpty::isValid(0));
        $this->assertFalse(EnumConcreteEmpty::isValid(null));
        $this->assertFalse(EnumConcreteEmpty::isValid(''));

        $enum = new EnumConcrete();
        $this->assertFalse(EnumConcrete::isValid(0));
        $this->assertFalse(EnumConcrete::isValid(null));
        $this->assertFalse(EnumConcrete::isValid(''));
        $this->assertFalse(EnumConcrete::isValid(false));
        $this->assertFalse(EnumConcrete::isValid(true));

        $this->assertTrue(EnumConcrete::isValid(666));
        $this->assertFalse(EnumConcrete::isValid('666'));
        $this->assertFalse(EnumConcrete::isValid(666.0));

        $this->assertTrue(EnumConcrete::isValid('X'));
    }

    public function testGetValue()
    {
        $enum = new EnumConcreteEmpty();
        $this->assertNull($enum->getValue());

        $enum = new EnumConcrete();
        $this->assertNull($enum->getValue());
        $this->assertNull($enum->getName());
        $this->assertNull($enum->getDescription());

        $this->assertNull($enum->getValueName());
        $this->assertNull($enum->getValueDescription());

        $enum = new EnumConcrete(EnumConcrete::STRING1);
        $this->assertEquals(EnumConcrete::STRING1, $enum->getValue());
        $this->assertEquals('string1', $enum->getName($enum->getValue()));
        $this->assertEquals('string1', $enum->getDescription($enum->getValue()));
        $this->assertEquals('string1', $enum->getValueName());
        $this->assertEquals('string1', $enum->getValueDescription());
        $this->assertNull($enum->getName());
        $this->assertNull($enum->getDescription());

        $enum = new EnumConcrete(EnumConcrete::STRING2);
        $this->assertEquals(EnumConcrete::STRING2, $enum->getValue());
        $this->assertEquals('string2', $enum->getName($enum->getValue()));
        $this->assertEquals('string with description', $enum->getDescription($enum->getValue()));
        $this->assertEquals('string2', $enum->getValueName());
        $this->assertEquals('string with description', $enum->getValueDescription());
        $this->assertNull($enum->getName());
        $this->assertNull($enum->getDescription());

        $enum = new EnumConcrete(EnumConcrete::NUMERIC1);
        $this->assertEquals(EnumConcrete::NUMERIC1, $enum->getValue());
        $this->assertEquals('numeric1', $enum->getName($enum->getValue()));
        $this->assertEquals('numeric1', $enum->getDescription($enum->getValue()));
        $this->assertEquals('numeric1', $enum->getValueName());
        $this->assertEquals('numeric1', $enum->getValueDescription());
        $this->assertNull($enum->getName());
        $this->assertNull($enum->getDescription());

        $enum = new EnumConcrete(EnumConcrete::NUMERIC2);
        $this->assertEquals(EnumConcrete::NUMERIC2, $enum->getValue());
        $this->assertEquals('numeric2', $enum->getName($enum->getValue()));
        $this->assertEquals('numeric with description', $enum->getDescription($enum->getValue()));
        $this->assertEquals('numeric2', $enum->getValueName());
        $this->assertEquals('numeric with description', $enum->getValueDescription());
        $this->assertNull($enum->getName());
        $this->assertNull($enum->getDescription());
    }

    public function testGetValueStatic()
    {
        $this->assertEquals([], EnumConcreteEmpty::getValues());
        $this->assertEquals(['S', 'X', 666, 999], EnumConcrete::getValues());
    }

    public function testIs()
    {
        $enum = new EnumConcrete(EnumConcrete::STRING1);
        $this->assertTrue($enum->is(EnumConcrete::STRING1));
        $this->assertFalse($enum->is('Z'));
        $this->assertFalse($enum->is(null));
        $this->assertFalse($enum->is(''));
        $this->assertFalse($enum->is(EnumConcrete::STRING2));
        $this->assertFalse($enum->is(EnumConcrete::NUMERIC1));
        $this->assertFalse($enum->is(EnumConcrete::NUMERIC2));

        $enum = new EnumConcrete(EnumConcrete::NUMERIC1);
        $this->assertTrue($enum->is(EnumConcrete::NUMERIC1));
        $this->assertFalse($enum->is('666'));
        $this->assertFalse($enum->is(666.0));
        $this->assertFalse($enum->is('Z'));
        $this->assertFalse($enum->is(null));
        $this->assertFalse($enum->is(''));
        $this->assertFalse($enum->is(0));
        $this->assertFalse($enum->is(INF));
        $this->assertFalse($enum->is(NAN));
        $this->assertFalse($enum->is(EnumConcrete::STRING1));
        $this->assertFalse($enum->is(EnumConcrete::STRING2));
        $this->assertFalse($enum->is(EnumConcrete::NUMERIC2));
    }

    public function testValue()
    {
        $empty = new EnumConcrete();
        $this->assertNull($empty->getValue());

        $numeric = new EnumConcrete(EnumConcrete::NUMERIC1);
        $this->assertEquals(EnumConcrete::NUMERIC1, $numeric->getValue());

        $string = new EnumConcrete(EnumConcrete::STRING1);
        $this->assertEquals(EnumConcrete::STRING1, $string->getValue());

        $this->assertNotEquals(EnumConcrete::STRING1, $numeric->getValue());
        $this->assertNotEquals(EnumConcrete::NUMERIC1, $string->getValue());
    }
}

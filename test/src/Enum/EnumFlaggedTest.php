<?php

namespace RealejoTest\Enum;

use PHPUnit\Framework\TestCase;

class EnumFlaggedTest extends TestCase
{

    public function testGetName(): void
    {
        $enum = new EnumFlaggedConcreteEmpty();
        $this->assertEquals([], $enum::getNames());
        $this->assertNull($enum::getName());

        $enum = new EnumFlaggedConcrete();
        $this->assertEquals(
            [
                1 => 'x',
                2 => 'w',
                4 => 'r'
            ], $enum::getNames()
        );
        $this->assertNull($enum::getName());
        $this->assertNull($enum::getName('Z'));
        $this->assertNull($enum::getName(8));
        $this->assertEquals('x', $enum::getName(1));
        $this->assertEquals('w', $enum::getName(2));
        $this->assertEquals('r', $enum::getName(4));
        $this->assertNull($enum::getName('1'));
        $this->assertNull($enum::getName('2'));
        $this->assertNull($enum::getName('4'));

        $this->assertNull($enum->getValueName());
        $this->assertNull($enum->getValueName('Z'));
        $this->assertNull($enum->getValueName(8));
        $this->assertEquals('x', $enum->getValueName(1));
        $this->assertEquals('w', $enum->getValueName(2));
        $this->assertEquals('r', $enum->getValueName(4));
        $this->assertNull($enum->getValueName('1'));
        $this->assertNull($enum->getValueName('2'));
        $this->assertNull($enum->getValueName('4'));


        $this->assertEquals('x/w', $enum::getName(1 | 2));
        $this->assertEquals('xw', $enum::getName(1 | 2, ''));
        $this->assertEquals(
            [
                1 => 'x',
                2 => 'w',
            ], $enum::getName(1 | 2, false)
        );

        $this->assertEquals('x/w', $enum->getValueName(1 | 2));
        $this->assertEquals('xw', $enum->getValueName(1 | 2, ''));
        $this->assertEquals(
            [
                1 => 'x',
                2 => 'w',
            ], $enum->getValueName(1 | 2, false)
        );
    }

    public function testGetNameStatic(): void
    {
        $this->assertEquals([], EnumFlaggedConcreteEmpty::getNames());
        $this->assertNull(EnumFlaggedConcreteEmpty::getName());

        $this->assertEquals(
            [
                1 => 'x',
                2 => 'w',
                4 => 'r'
            ], EnumFlaggedConcrete::getNames()
        );
        $this->assertNull(EnumFlaggedConcrete::getName());
        $this->assertNull(EnumFlaggedConcrete::getName('Z'));
        $this->assertNull(EnumFlaggedConcrete::getName(8));
        $this->assertEquals('x', EnumFlaggedConcrete::getName(1));
        $this->assertEquals('w', EnumFlaggedConcrete::getName(2));
        $this->assertEquals('r', EnumFlaggedConcrete::getName(4));
        $this->assertNull(EnumFlaggedConcrete::getName('1'));
        $this->assertNull(EnumFlaggedConcrete::getName('2'));
        $this->assertNull(EnumFlaggedConcrete::getName('4'));

        $this->assertEquals('x/w', EnumFlaggedConcrete::getName(1 | 2));
        $this->assertEquals('xw', EnumFlaggedConcrete::getName(1 | 2, ''));
        $this->assertEquals(
            [
                1 => 'x',
                2 => 'w',
            ], EnumFlaggedConcrete::getName(1 | 2, false)
        );
    }

    public function testGetDescription(): void
    {
        $enum = new EnumFlaggedConcreteEmpty();
        $this->assertEquals([], $enum::getDescriptions());
        $this->assertNull($enum::getDescription());
        $this->assertNull($enum->getValueDescription());

        $enum = new EnumFlaggedConcrete();
        $this->assertEquals(
            [
                1 => 'execute',
                2 => 'w',
                4 => 'r'
            ], $enum::getDescriptions()
        );
        $this->assertNull($enum::getDescription());
        $this->assertNull($enum::getDescription('Z'));
        $this->assertNull($enum::getDescription(8));
        $this->assertEquals('execute', $enum::getDescription(1));
        $this->assertEquals('w', $enum::getDescription(2));
        $this->assertEquals('r', $enum::getDescription(4));
        $this->assertNull($enum::getDescription('1'));
        $this->assertNull($enum::getDescription('2'));
        $this->assertNull($enum::getDescription('4'));

        $this->assertNull($enum->getValueDescription());
        $this->assertNull($enum->getValueDescription('Z'));
        $this->assertNull($enum->getValueDescription(8));
        $this->assertEquals('execute', $enum->getValueDescription(1));
        $this->assertEquals('w', $enum->getValueDescription(2));
        $this->assertEquals('r', $enum->getValueDescription(4));
        $this->assertNull($enum->getValueDescription('1'));
        $this->assertNull($enum->getValueDescription('2'));
        $this->assertNull($enum->getValueDescription('4'));

        $this->assertEquals('execute/w', $enum::getDescription(1 | 2));
        $this->assertEquals('executew', $enum::getDescription(1 | 2, ''));
        $this->assertEquals(
            [
                1 => 'execute',
                2 => 'w',
            ], $enum::getDescription(1 | 2, false)
        );

        $this->assertEquals('execute/w', $enum->getValueDescription(1 | 2));
        $this->assertEquals('executew', $enum->getValueDescription(1 | 2, ''));
        $this->assertEquals(
            [
                1 => 'execute',
                2 => 'w',
            ], $enum->getValueDescription(1 | 2, false)
        );
    }

    public function testGetDescriptionStatic(): void
    {
        $this->assertEquals([], EnumFlaggedConcreteEmpty::getDescriptions());
        $this->assertNull(EnumFlaggedConcreteEmpty::getDescription());

        $this->assertEquals(
            [
                1 => 'execute',
                2 => 'w',
                4 => 'r'
            ], EnumFlaggedConcrete::getDescriptions()
        );
        $this->assertNull(EnumFlaggedConcrete::getDescription());
        $this->assertNull(EnumFlaggedConcrete::getDescription('Z'));
        $this->assertNull(EnumFlaggedConcrete::getDescription(8));
        $this->assertEquals('execute', EnumFlaggedConcrete::getDescription(1));
        $this->assertEquals('w', EnumFlaggedConcrete::getDescription(2));
        $this->assertEquals('r', EnumFlaggedConcrete::getDescription(4));
        $this->assertNull(EnumFlaggedConcrete::getDescription('1'));
        $this->assertNull(EnumFlaggedConcrete::getDescription('2'));
        $this->assertNull(EnumFlaggedConcrete::getDescription('4'));

        $this->assertEquals('execute/w', EnumFlaggedConcrete::getDescription(1 | 2));
        $this->assertEquals('executew', EnumFlaggedConcrete::getDescription(1 | 2, ''));
        $this->assertEquals(
            [
                1 => 'execute',
                2 => 'w',
            ], EnumFlaggedConcrete::getDescription(1 | 2, false)
        );
    }

    public function testGetValueStatic(): void
    {
        $this->assertEquals([], EnumFlaggedConcreteEmpty::getValues());
        $this->assertEquals([1, 2, 4], EnumFlaggedConcrete::getValues());
        $this->assertEquals([1 << 0, 1 << 1, 1 << 2], EnumFlaggedConcrete::getValues());
    }

    public function testIsValid(): void
    {
        $enum = new EnumFlaggedConcreteEmpty();
        $this->assertTrue($enum::isValid(0));
        $this->assertFalse($enum::isValid(null));
        $this->assertFalse($enum::isValid(''));
        $this->assertFalse($enum::isValid(1));

        $enum = new EnumFlaggedConcrete();
        $this->assertTrue($enum::isValid(0));
        $this->assertFalse($enum::isValid(null));
        $this->assertFalse($enum::isValid(''));
        $this->assertFalse($enum::isValid(false));
        $this->assertFalse($enum::isValid(true));

        $this->assertTrue($enum::isValid(1));
        $this->assertFalse($enum::isValid('1'));
        $this->assertFalse($enum::isValid(1.0));

        $this->assertTrue($enum::isValid(2));
        $this->assertFalse($enum::isValid('2'));
        $this->assertFalse($enum::isValid(2.0));

        $this->assertTrue($enum::isValid(3));
        $this->assertFalse($enum::isValid('3'));
        $this->assertFalse($enum::isValid(3.0));

        $this->assertTrue($enum::isValid(7));
        $this->assertFalse($enum::isValid('7'));
        $this->assertFalse($enum::isValid(7.0));
    }

    public function testIsValidStatic(): void
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

        $this->assertTrue(EnumFlaggedConcrete::isValid(7));
        $this->assertFalse(EnumFlaggedConcrete::isValid('7'));
        $this->assertFalse(EnumFlaggedConcrete::isValid(7.0));

        $this->assertFalse(EnumFlaggedConcrete::isValid(8));
        $this->assertFalse(EnumFlaggedConcrete::isValid('8'));
        $this->assertFalse(EnumFlaggedConcrete::isValid(8.0));

        $this->assertTrue(EnumFlaggedConcrete::isValid(EnumFlaggedConcrete::READ));
        $this->assertTrue(EnumFlaggedConcrete::isValid(EnumFlaggedConcrete::WRITE));
        $this->assertTrue(EnumFlaggedConcrete::isValid(EnumFlaggedConcrete::EXECUTE));

        $this->assertTrue(
            EnumFlaggedConcrete::isValid(
                EnumFlaggedConcrete::READ | EnumFlaggedConcrete::WRITE | EnumFlaggedConcrete::EXECUTE
            )
        );
        $this->assertFalse(
            EnumFlaggedConcrete::isValid(
                (EnumFlaggedConcrete::READ | EnumFlaggedConcrete::WRITE | EnumFlaggedConcrete::EXECUTE) + 1
            )
        );
    }

    public function testGetValue(): void
    {
        $enum = new EnumFlaggedConcreteEmpty();
        $this->assertEquals(0, $enum->getValue());

        $enum = new EnumFlaggedConcrete();
        $this->assertEquals(0, $enum->getValue());
        $this->assertNull($enum::getName());
        $this->assertNull($enum::getDescription());

        $enum = new EnumFlaggedConcrete(EnumFlaggedConcrete::EXECUTE);
        $this->assertEquals(EnumFlaggedConcrete::EXECUTE, $enum->getValue());
        $this->assertEquals('x', $enum::getName($enum->getValue()));
        $this->assertEquals('execute', $enum::getDescription($enum->getValue()));
        $this->assertEquals('x', $enum->getValueName());
        $this->assertEquals('execute', $enum->getValueDescription());
        $this->assertNull($enum::getName());
        $this->assertNull($enum::getDescription());

        $enum = new EnumFlaggedConcrete(EnumFlaggedConcrete::WRITE);
        $this->assertEquals(EnumFlaggedConcrete::WRITE, $enum->getValue());
        $this->assertEquals('w', $enum::getName($enum->getValue()));
        $this->assertEquals('w', $enum::getDescription($enum->getValue()));
        $this->assertEquals('w', $enum->getValueName());
        $this->assertEquals('w', $enum->getValueDescription());
        $this->assertNull($enum::getName());
        $this->assertNull($enum::getDescription());

        $enum = new EnumFlaggedConcrete(EnumFlaggedConcrete::READ);
        $this->assertEquals(EnumFlaggedConcrete::READ, $enum->getValue());
        $this->assertEquals('r', $enum::getName($enum->getValue()));
        $this->assertEquals('r', $enum::getDescription($enum->getValue()));
        $this->assertEquals('r', $enum->getValueName());
        $this->assertEquals('r', $enum->getValueDescription());
        $this->assertNull($enum::getName());
        $this->assertNull($enum::getDescription());

        $enum = new EnumFlaggedConcrete(EnumFlaggedConcrete::READ | EnumFlaggedConcrete::WRITE);
        $this->assertEquals(6, $enum->getValue());
        $this->assertEquals('w/r', $enum::getName($enum->getValue()));
        $this->assertEquals('w/r', $enum::getDescription($enum->getValue()));
        $this->assertEquals('w/r', $enum->getValueName());
        $this->assertEquals('w/r', $enum->getValueDescription());
        $this->assertNull($enum::getName());
        $this->assertNull($enum::getDescription());
    }

    public function testIsHas(): void
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

        $enum = new EnumFlaggedConcrete(EnumFlaggedConcrete::READ | EnumFlaggedConcrete::WRITE);
        $this->assertEquals(EnumFlaggedConcrete::READ | EnumFlaggedConcrete::WRITE, $enum->getValue());
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

    public function testValue(): void
    {
        $empty = new EnumFlaggedConcrete();
        $this->assertEquals(0, $empty->getValue());
        $this->assertNull($empty->getValueName());
        $this->assertNull($empty->getValueDescription());

        $write = new EnumFlaggedConcrete(EnumFlaggedConcrete::WRITE);
        $this->assertEquals(EnumFlaggedConcrete::WRITE, $write->getValue());

        $read = new EnumFlaggedConcrete(EnumFlaggedConcrete::READ);
        $this->assertEquals(EnumFlaggedConcrete::READ, $read->getValue());

        $this->assertNotEquals(EnumFlaggedConcrete::READ, $write->getValue());
        $this->assertNotEquals(EnumFlaggedConcrete::WRITE, $read->getValue());
    }

    public function testAddRemove():void
    {
        $enum  = new EnumFlaggedConcrete();
        $this->assertEquals(0, $enum->getValue());
        $this->assertFalse($enum->has(EnumFlaggedConcrete::EXECUTE));
        $this->assertFalse($enum->has(EnumFlaggedConcrete::WRITE));
        $this->assertFalse($enum->has(EnumFlaggedConcrete::READ));

        $enum->add(EnumFlaggedConcrete::READ);
        $this->assertFalse($enum->has(EnumFlaggedConcrete::EXECUTE));
        $this->assertFalse($enum->has(EnumFlaggedConcrete::WRITE));
        $this->assertTrue($enum->has(EnumFlaggedConcrete::READ));

        $enum->add(EnumFlaggedConcrete::WRITE);
        $this->assertFalse($enum->has(EnumFlaggedConcrete::EXECUTE));
        $this->assertTrue($enum->has(EnumFlaggedConcrete::WRITE));
        $this->assertTrue($enum->has(EnumFlaggedConcrete::READ));

        $enum->add(0);
        $this->assertFalse($enum->has(EnumFlaggedConcrete::EXECUTE));
        $this->assertTrue($enum->has(EnumFlaggedConcrete::WRITE));
        $this->assertTrue($enum->has(EnumFlaggedConcrete::READ));
        $enum->remove(0);
        $this->assertFalse($enum->has(EnumFlaggedConcrete::EXECUTE));
        $this->assertTrue($enum->has(EnumFlaggedConcrete::WRITE));
        $this->assertTrue($enum->has(EnumFlaggedConcrete::READ));

        $enum->remove(EnumFlaggedConcrete::WRITE);
        $this->assertFalse($enum->has(EnumFlaggedConcrete::EXECUTE));
        $this->assertFalse($enum->has(EnumFlaggedConcrete::WRITE));
        $this->assertTrue($enum->has(EnumFlaggedConcrete::READ));

        $enum->add(EnumFlaggedConcrete::EXECUTE);
        $enum->add(EnumFlaggedConcrete::EXECUTE);
        $enum->add(EnumFlaggedConcrete::EXECUTE);
        $this->assertTrue($enum->has(EnumFlaggedConcrete::EXECUTE));
        $this->assertFalse($enum->has(EnumFlaggedConcrete::WRITE));
        $this->assertTrue($enum->has(EnumFlaggedConcrete::READ));

        $enum->remove(EnumFlaggedConcrete::READ);
        $this->assertTrue($enum->has(EnumFlaggedConcrete::EXECUTE));
        $this->assertFalse($enum->has(EnumFlaggedConcrete::WRITE));
        $this->assertFalse($enum->has(EnumFlaggedConcrete::READ));

        $enum->remove(EnumFlaggedConcrete::EXECUTE);
        $enum->remove(EnumFlaggedConcrete::EXECUTE);
        $enum->remove(EnumFlaggedConcrete::EXECUTE);
        $this->assertFalse($enum->has(EnumFlaggedConcrete::EXECUTE));
        $this->assertFalse($enum->has(EnumFlaggedConcrete::WRITE));
        $this->assertFalse($enum->has(EnumFlaggedConcrete::READ));

        /**
         * Testes considerando o valor direto
         */
        $enum  = new EnumFlaggedConcrete();
        $enum->add(EnumFlaggedConcrete::EXECUTE);
        $this->assertEquals(EnumFlaggedConcrete::EXECUTE, $enum->getValue());
        $enum->add(EnumFlaggedConcrete::EXECUTE);
        $this->assertEquals(EnumFlaggedConcrete::EXECUTE, $enum->getValue());

        $enum->add(EnumFlaggedConcrete::WRITE);
        $this->assertEquals(EnumFlaggedConcrete::EXECUTE|EnumFlaggedConcrete::WRITE, $enum->getValue());

        $enum->remove(EnumFlaggedConcrete::EXECUTE);
        $this->assertEquals(EnumFlaggedConcrete::WRITE, $enum->getValue());
        $enum->remove(EnumFlaggedConcrete::EXECUTE);
        $this->assertEquals(EnumFlaggedConcrete::WRITE, $enum->getValue());
    }

    /**
     * @expectedExceptionMessage Value '123' is not valid.
     * @expectedException \InvalidArgumentException
     */
    public function testAddException():void
    {
        $enum  = new EnumFlaggedConcrete();
        $enum->add(123);
    }

    /**
     * @expectedExceptionMessage Value '123' is not valid.
     * @expectedException \InvalidArgumentException
     */
    public function testRemoveException():void
    {
        $enum  = new EnumFlaggedConcrete();
        $enum->remove(123);
    }
}

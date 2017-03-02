<?php
namespace RealejoTest\Stdlib;

use Realejo\Stdlib\ArrayObject;

/**
 * ArrayObject test case.
 */
class ArrayObjectTest extends \PHPUnit\Framework\TestCase
{
    /**
     * Tests ArrayObject->populate()
     */
    public function testPopulateToArray()
    {
        $object = new ArrayObject();
        $this->assertNotNull($object->toArray());
        $this->assertEmpty($object->toArray());
        $this->assertEquals([], $object->toArray());

        $this->assertNull($object->populate(['one' => 'first']));

        $this->assertNotNull($object->toArray());
        $this->assertNotEmpty($object->toArray());
        $this->assertEquals(['one' => 'first'], $object->toArray());
        $this->assertEquals($object->toArray(), $object->entityToArray());
        $this->assertEquals($object->toArray(), $object->getArrayCopy());
        $this->assertEquals('first', $object->one);
        $this->assertEquals('first', $object['one']);

        $object = new ArrayObject(['two' => 'second']);
        $this->assertNotNull($object->toArray());
        $this->assertNotEmpty($object->toArray());
        $this->assertEquals(['two' => 'second'], $object->toArray());
        $this->assertEquals($object->toArray(), $object->entityToArray());
        $this->assertEquals($object->toArray(), $object->getArrayCopy());
        $this->assertEquals('second', $object->two);
        $this->assertEquals('second', $object['two']);

        $stdClass = (object) ['three' => 'third'];
        $object = new ArrayObject(['two' => $stdClass]);
        $this->assertNotNull($object->toArray());
        $this->assertNotEmpty($object->toArray());
        $this->assertEquals(['two' => $stdClass], $object->toArray());
        $this->assertEquals($object->toArray(), $object->entityToArray());
        $this->assertEquals($object->toArray(), $object->getArrayCopy());
        $this->assertEquals($stdClass, $object->two);
        $this->assertEquals($stdClass, $object['two']);
    }

    /**
     * Tests ArrayObject->populate()
     */
    public function testSetGet()
    {
        $object = new ArrayObject();
        $this->assertNotNull($object->toArray());
        $this->assertEmpty($object->toArray());
        $this->assertEquals([], $object->toArray());
        $this->assertEquals($object->toArray(), $object->entityToArray());
        $this->assertEquals($object->toArray(), $object->getArrayCopy());

        // Desabilita o bloqueio de chaves
        $this->assertInstanceof(get_class($object), $object->setLockedKeys(false));

        $object->one = 'first';
        $this->assertNotNull($object->toArray());
        $this->assertNotEmpty($object->toArray());
        $this->assertEquals('first', $object->one);
        $this->assertEquals('first', $object['one']);
        $this->assertEquals(['one' => 'first'], $object->toArray());
        $this->assertEquals($object->toArray(), $object->entityToArray());
        $this->assertEquals($object->toArray(), $object->getArrayCopy());
        $this->assertTrue(isset($object->one));
        $this->assertTrue(isset($object['one']));
        unset($object->one);
        $this->assertNotNull($object->toArray());
        $this->assertEmpty($object->toArray());
        $this->assertEquals([], $object->toArray());
        $this->assertFalse(isset($object->one));
        $this->assertFalse(isset($object['one']));

        $object['two'] = 'second';
        $this->assertNotNull($object->toArray());
        $this->assertNotEmpty($object->toArray());
        $this->assertEquals(['two' => 'second'], $object->toArray());
        $this->assertEquals($object->toArray(), $object->entityToArray());
        $this->assertEquals($object->toArray(), $object->getArrayCopy());
        $this->assertEquals('second', $object->two);
        $this->assertEquals('second', $object['two']);
        $this->assertTrue(isset($object->two));
        $this->assertTrue(isset($object['two']));
        unset($object['two']);
        $this->assertNotNull($object->toArray());
        $this->assertEmpty($object->toArray());
        $this->assertEquals([], $object->toArray());
        $this->assertEquals($object->toArray(), $object->entityToArray());
        $this->assertEquals($object->toArray(), $object->getArrayCopy());
        $this->assertFalse(isset($object->two));
        $this->assertFalse(isset($object['two']));

        $stdClass = (object) ['three' => 'third'];

        $object['two'] = $stdClass;
        $this->assertNotNull($object->toArray());
        $this->assertNotEmpty($object->toArray());
        $this->assertEquals(['two' => $stdClass], $object->toArray());
        $this->assertEquals($object->toArray(), $object->entityToArray());
        $this->assertEquals($object->toArray(), $object->getArrayCopy());
        $this->assertEquals($stdClass, $object->two);
        $this->assertEquals($stdClass, $object['two']);
        $this->assertTrue(isset($object->two));
        $this->assertTrue(isset($object['two']));
        unset($object['two']);
        $this->assertNotNull($object->toArray());
        $this->assertEmpty($object->toArray());
        $this->assertEquals([], $object->toArray());
        $this->assertEquals($object->toArray(), $object->entityToArray());
        $this->assertEquals($object->toArray(), $object->getArrayCopy());
        $this->assertFalse(isset($object->two));
        $this->assertFalse(isset($object['two']));
    }

    /**
     * @expectedException PHPUnit_Framework_Error_Notice
     */
    function testGetKeyNonExisting()
    {
        $object = new ArrayObject();
        $this->assertFalse(isset($object['test']));
        $object['test'];
    }

    /**
     * @expectedException PHPUnit_Framework_Error_Notice
     */
    function testGetPropertyNonExisting()
    {
        $object = new ArrayObject();
        $this->assertFalse(isset($object->test));
        $object->test;
    }

    /**
     * @expectedException PHPUnit_Framework_Error_Notice
     */
    function testGetKeyNonExistingWithNoLockedKeys()
    {
        $object = new ArrayObject();
        $object->setLockedKeys(false);
        $this->assertFalse(isset($object['test']));
        $this->assertNull($object['test']);
        $object['test'];
    }

    /**
     * @expectedException PHPUnit_Framework_Error_Notice
     */
    function testGetPropertyNonExistingWithNoLockedKeys()
    {
        $object = new ArrayObject();
        $object->setLockedKeys(false);
        $this->assertFalse(isset($object->test));
        $this->assertNull($object->test);
    }

    /**
     * @expectedException PHPUnit_Framework_Error_Notice
     */
    function testSetKeyNonExisting()
    {
        $object = new ArrayObject();
        $this->assertFalse(isset($object['test']));
        $object['test'] = 'tessst';
    }

    /**
     * @expectedException PHPUnit_Framework_Error_Notice
     */
    function testSetPropertyNonExisting()
    {
        $object = new ArrayObject();
        $this->assertFalse(isset($object->test));
        $object->test = 'tessst';
    }

    /**
     * @expectedException Exception
     */
    function testUnsetKeyNonExisting()
    {
        $object = new ArrayObject();
        $this->assertFalse(isset($object['test']));
        unset($object['test']);
    }

    /**
     * @expectedException Exception
     */
    function testUnsetPropertyNonExisting()
    {
        $object = new ArrayObject();
        $this->assertFalse(isset($object->test));
        unset($object->test);
    }

    /**
     * Tests ArrayObject::getMapNaming()
     */
    public function testDeprecatedMapping()
    {
        $object = new ArrayObject();
        $this->assertNull($object->getDeprecatedMapping());
        $this->assertInstanceof(get_class($object), $object->setDeprecatedMapping(['one' => 'two']));
        $this->assertNotNull($object->getDeprecatedMapping());
        $this->assertEquals(['one' => 'two'], $object->getDeprecatedMapping());

        $object->populate(['one' => 'first']);
        $this->assertTrue(isset($object->one));
        $this->assertTrue(isset($object->one));
        $this->assertTrue(isset($object->two));
        $this->assertTrue(isset($object->two));

        $object = new ArrayObject();
        $this->assertNull($object->getDeprecatedMapping());
        $this->assertInstanceof(get_class($object), $object->setDeprecatedMapping(['one' => 'two']));
        $this->assertNotNull($object->getDeprecatedMapping());
        $this->assertEquals(['one' => 'two'], $object->getDeprecatedMapping());
        $this->assertInstanceof(get_class($object), $object->setDeprecatedMapping(null));
        $this->assertNull($object->getDeprecatedMapping());
        $this->assertEquals(null, $object->getDeprecatedMapping());
    }
}

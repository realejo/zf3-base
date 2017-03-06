<?php
namespace RealejoTest\Stdlib;

use PHPUnit\Framework\TestCase;
use Realejo\Stdlib\ArrayObject;

/**
 * ArrayObject test case.
 */
class ArrayObjectTest extends TestCase
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
     * @expectedException \PHPUnit_Framework_Error_Notice
     */
    public function testGetKeyNonExisting()
    {
        $object = new ArrayObject();
        $this->assertFalse(isset($object['test']));
        $object['test'];
    }

    /**
     * @expectedException \PHPUnit_Framework_Error_Notice
     */
    public function testGetPropertyNonExisting()
    {
        $object = new ArrayObject();
        $this->assertFalse(isset($object->test));
        $object->test;
    }

    /**
     * @expectedException \PHPUnit_Framework_Error_Notice
     */
    public function testGetKeyNonExistingWithNoLockedKeys()
    {
        $object = new ArrayObject();
        $object->setLockedKeys(false);
        $this->assertFalse(isset($object['test']));
        $this->assertNull($object['test']);
        $object['test'];
    }

    /**
     * @expectedException \PHPUnit_Framework_Error_Notice
     */
    public function testGetPropertyNonExistingWithNoLockedKeys()
    {
        $object = new ArrayObject();
        $object->setLockedKeys(false);
        $this->assertFalse(isset($object->test));
        $this->assertNull($object->test);
    }

    /**
     * @expectedException \PHPUnit_Framework_Error_Notice
     */
    public function testSetKeyNonExisting()
    {
        $object = new ArrayObject();
        $this->assertFalse(isset($object['test']));
        $object['test'] = 'tessst';
    }

    /**
     * @expectedException \PHPUnit_Framework_Error_Notice
     */
    public function testSetPropertyNonExisting()
    {
        $object = new ArrayObject();
        $this->assertFalse(isset($object->test));
        $object->test = 'tessst';
    }

    /**
     * @expectedException \Exception
     */
    public function testUnsetKeyNonExisting()
    {
        $object = new ArrayObject();
        $this->assertFalse(isset($object['test']));
        unset($object['test']);
    }

    /**
     * @expectedException \Exception
     */
    public function testUnsetPropertyNonExisting()
    {
        $object = new ArrayObject();
        $this->assertFalse(isset($object->test));
        unset($object->test);
    }

    /**
     * Tests ArrayObject::getMapNaming()
     */
    public function testMapping()
    {
        $object = new ArrayObject();
        $this->assertNull($object->getKeyMapping());
        $this->assertInstanceof(get_class($object), $object->setMapping(['original' => 'mapped']));
        $this->assertNotNull($object->getKeyMapping());
        $this->assertEquals(['original' => 'mapped'], $object->getKeyMapping());

        $object->populate(['original' => 'realValue']);

        $this->assertTrue(isset($object->original), 'A chave original será mapeada para a nova');
        $this->assertTrue(isset($object->mapped) , 'A chave mapeada está disponível');
        $this->assertEquals('realValue', $object->original);
        $this->assertEquals('realValue', $object->mapped);

        $objectArray = $object->toArray();
        $this->assertCount(1, $objectArray);
        $this->assertEquals(['original'=>'realValue'], $objectArray);

        $object = new ArrayObject();
        $this->assertNull($object->getKeyMapping());
        $this->assertInstanceof(get_class($object), $object->setMapping(['one' => 'two']));
        $this->assertNotNull($object->getKeyMapping());
        $this->assertEquals(['one' => 'two'], $object->getKeyMapping());
        $this->assertInstanceof(get_class($object), $object->setMapping(null));
        $this->assertNull($object->getKeyMapping());
        $this->assertEquals(null, $object->getKeyMapping());
    }
}

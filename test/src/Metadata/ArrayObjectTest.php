<?php
namespace RealejoTest\Metadata;

use Realejo\Metadata\ArrayObject;
use Realejo\Metadata\MetadataArrayObject;

/**
 * ArrayObject test case.
 */
class ArrayObjectTest extends \PHPUnit\Framework\TestCase
{

    /**
     * Tests ArrayObject->getMetadata()
     */
    public function testMetadata()
    {
        $object = new ArrayObject();
        $this->assertNotNull($object->getMetadata());
        $this->assertEmpty($object->getMetadata());
        $this->assertInstanceOf('\Realejo\Stdlib\ArrayObject', $object);

        $metadata = new MetadataArrayObject(array('one'=>'first'));

        $this->assertInstanceOf('\Realejo\Metadata\ArrayObject', $object->setMetadata($metadata));
        $this->assertInstanceOf('\Realejo\Metadata\MetadataArrayObject', $object->getMetadata());
        $this->assertEquals(array('one'=>'first'), $object->getMetadata()->toArray());

        $this->assertTrue($object->hasMetadata('one'));
        $this->assertFalse($object->hasMetadata('two'));

        $metadata = array('two'=>'second');

        $this->assertInstanceOf('\Realejo\Metadata\ArrayObject', $object->setMetadata($metadata));
        $this->assertInstanceOf('\Realejo\Metadata\MetadataArrayObject', $object->getMetadata());

        $this->assertTrue($object->hasMetadata('two'));
        $this->assertFalse($object->hasMetadata('one'));

    }

    /**
     * Tests ArrayObject->addMetadata()
     */
    public function testAddMetadata()
    {
        $object = new ArrayObject();
        $this->assertEmpty($object->getMetadata());
        $this->assertInstanceOf('\Realejo\Stdlib\ArrayObject', $object);

        $metadata = new MetadataArrayObject(array('one'=>'first'));

        $this->assertInstanceOf('\Realejo\Metadata\ArrayObject', $object->setMetadata($metadata));
        $this->assertInstanceOf('\Realejo\Metadata\MetadataArrayObject', $object->getMetadata());

        $this->assertFalse($object->hasMetadata('two'));
        $this->assertTrue($object->hasMetadata('one'));

        $metadata = array('two'=>'second');

        $this->assertInstanceOf('\Realejo\Metadata\ArrayObject', $object->addMetadata($metadata));
        $this->assertInstanceOf('\Realejo\Metadata\MetadataArrayObject', $object->getMetadata());

        $this->assertTrue($object->hasMetadata('two'));
        $this->assertTrue($object->hasMetadata('one'));
    }

    public function testPopulateToArray()
    {
        $object = new ArrayObject(array('one'=>'first'));
        $this->assertInstanceOf('\Realejo\Metadata\MetadataArrayObject', $object->getMetadata());
        $this->assertCount(0, $object->getMetadata());
        $this->assertEmpty($object->getMetadata());

        $this->assertTrue(isset($object->one));
        $this->assertTrue(isset($object['one']));

        $this->assertFalse(isset($object->two));
        $this->assertFalse(isset($object['two']));

        $this->assertEquals(array('one'=>'first'), $object->toArray());

        $object = new ArrayObject(array('one'=>'first', 'metadata'=>array('two'=>'second')));
        $this->assertInstanceOf('\Realejo\Metadata\MetadataArrayObject', $object->getMetadata());
        $this->assertCount(1, $object->getMetadata());

        $this->assertTrue(isset($object->one));
        $this->assertTrue(isset($object['one']));

        $this->assertTrue(isset($object->two));
        $this->assertTrue(isset($object['two']));

        $this->assertFalse(isset($object->three));
        $this->assertFalse(isset($object['three']));

        $this->assertEquals(array('two' => 'second'), $object->getMetadata()->toArray());
        $this->assertEquals(array('one'=>'first', 'metadata'=> array('two' => 'second')), $object->toArray());

        $object = new ArrayObject(array('one'=>'first', 'metadata'=>'{"two":"second"}'));
        $this->assertInstanceOf('\Realejo\Metadata\MetadataArrayObject', $object->getMetadata());
        $this->assertCount(1, $object->getMetadata());

        $this->assertTrue(isset($object->one));
        $this->assertTrue(isset($object['one']));

        $this->assertTrue(isset($object->two));
        $this->assertTrue(isset($object['two']));

        $this->assertFalse(isset($object->three));
        $this->assertFalse(isset($object['three']));

        $this->assertEquals(array('two' => 'second'), $object->getMetadata()->toArray());
        $this->assertEquals(array('one'=>'first', 'metadata'=> array('two' => 'second')), $object->toArray());
    }

    /**
     * Tests ArrayObject->hasMetadata()
     */
    public function testGetterSetter()
    {
        $object = new ArrayObject(array('one'=>'first', 'metadata'=>array('two'=>'second')));
        $this->assertInstanceOf('\Realejo\Metadata\MetadataArrayObject', $object->getMetadata());
        $this->assertCount(1, $object->getMetadata());

        $object->one = 'once';
        $this->assertEquals('once', $object->one);
        $this->assertEquals('once', $object['one']);

        $object['one'] = 'more';
        $this->assertEquals('more', $object->one);
        $this->assertEquals('more', $object['one']);

        $object->two = 'time';
        $this->assertEquals('time', $object->two);
        $this->assertEquals('time', $object['two']);

        $object['two'] = 'lets celebrate';
        $this->assertEquals('lets celebrate', $object->two);
        $this->assertEquals('lets celebrate', $object['two']);

        unset($object->two);
        $this->assertNull($object->two);
        $this->assertNull($object['two']);
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
}


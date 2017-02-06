<?php
namespace RealejoTest\Metadata;

use Realejo\Metadata\MetadataArrayObject;

/**
 * MetadataArrayObject test case.
 */
class MetadataArrayObjectTest extends \PHPUnit\Framework\TestCase
{

    public function testGettersSetters()
    {
        $object = new MetadataArrayObject();
        $this->assertEmpty($object);
        $this->assertFalse(isset($object->one));
        $this->assertFalse(isset($object['one']));

        $object->addMetadata(array('one'=>'first'));
        $this->assertNotEmpty($object);
        $this->assertEquals('first', $object->one);
        $this->assertEquals('first', $object['one']);

        $object->one = null;

        $this->assertNotEmpty($object);
        $this->assertEquals(null, $object->one);
        $this->assertEquals(null, $object['one']);
        $this->assertEquals(array('one'=>null), $object->toArray());

        $object->one = 'again';

        $this->assertNotEmpty($object);
        $this->assertEquals('again', $object->one);
        $this->assertEquals('again', $object['one']);
        $this->assertEquals(array('one'=>'again'), $object->toArray());

        $object->one = 'oncemore';

        $this->assertNotEmpty($object);
        $this->assertEquals('oncemore', $object->one);
        $this->assertEquals('oncemore', $object['one']);
        $this->assertEquals(array('one'=>'oncemore'), $object->toArray());

        $object->addMetadata(array('two'=>null));
        $this->assertNotEmpty($object);
        $this->assertNull($object->two);
        $this->assertNull($object['two']);

        $this->assertTrue(isset($object->one));
        $this->assertTrue(isset($object->two));

        $this->assertFalse(isset($object->three));
        $this->assertFalse(isset($object['three']));

        $this->assertFalse(empty($object->one));
        $this->assertTrue(empty($object->two));
        $this->assertTrue(empty($object->three));

        unset($object->one);

        $this->assertEquals(array('two'=>null, 'one'=>null), $object->toArray());

        $this->assertTrue(isset($object->one));
        $this->assertTrue(isset($object->two));

        $this->assertTrue(empty($object->one));
        $this->assertTrue(empty($object->two));
    }

    public function testCount()
    {
        $object = new MetadataArrayObject();
        $this->assertEmpty($object);
        $this->assertFalse(empty($object));

        $object = new MetadataArrayObject(array('one'=>'two'));
        $this->assertNotEmpty($object);
        $this->assertCount(1, $object);

        $object->one = null;
        $this->assertCount(1, $object);

        $object->addMetadata(array('two'=>'second'));
        $this->assertCount(2, $object);

        $object->two = null;
        $this->assertCount(2, $object);

        $object->one = 'first';
        $this->assertCount(2, $object);

        $object->addMetadata(array('three'=>null));
        $this->assertCount(3, $object);
    }

    /**
     * Tests MetadataArrayObject->populate()
     */
    public function testPopulateToArray()
    {
        $object = new MetadataArrayObject();
        $this->assertEmpty($object);
        $this->assertEmpty($object->toArray());

        $object = new MetadataArrayObject(array('one'=>'first'));
        $this->assertNotEmpty($object);
        $this->assertNotEmpty($object->toArray());
        $this->assertEquals(array('one'=>'first'), $object->toArray());

        $object->populate(array('two'=>'second'));
        $this->assertNotEmpty($object);
        $this->assertNotEmpty($object->toArray());
        $this->assertEquals(array('two'=>'second'), $object->toArray());

        $object->populate(array('third'=>null));
        $this->assertNotEmpty($object);
        $this->assertNotEmpty($object->toArray());
        $this->assertEquals(array('third'=>null), $object->toArray());
    }

    /**
     * @expectedException PHPUnit_Framework_Error_Notice
     */
    function testGetKeyNonExisting()
    {
        $object = new MetadataArrayObject();
        $object['test'];
    }

    /**
     * @expectedException PHPUnit_Framework_Error_Notice
     */
    function testGetPropertyNonExisting()
    {
        $object = new MetadataArrayObject();
        $object->test;
    }

    /**
     * @expectedException PHPUnit_Framework_Error_Notice
     */
    function testSetKeyNonExisting()
    {
        $object = new MetadataArrayObject();
        $object['test'] = 'tessst';
    }

    /**
     * @expectedException PHPUnit_Framework_Error_Notice
     */
    function testSetPropertyNonExisting()
    {
        $object = new MetadataArrayObject();
        $object->test = 'tessst';
    }

    /**
     * @expectedException PHPUnit_Framework_Error_Notice
     */
    function testUnsetKeyNonExisting()
    {
        $object = new MetadataArrayObject();
        unset($object['test']);
    }

    /**
     * @expectedException PHPUnit_Framework_Error_Notice
     */
    function testUnsetPropertyNonExisting()
    {
        $object = new MetadataArrayObject();
        unset($object->test);
    }
}


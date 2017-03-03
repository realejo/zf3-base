<?php
namespace RealejoTest\Service\Metadata;

use Realejo\Service\Metadata\MetadataArrayObject;

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

        $object->addMetadata(['one' => 'first']);
        $this->assertNotEmpty($object);
        $this->assertEquals('first', $object->one);
        $this->assertEquals('first', $object['one']);

        $object->one = null;

        $this->assertNotEmpty($object);
        $this->assertEquals(null, $object->one);
        $this->assertEquals(null, $object['one']);
        $this->assertEquals(['one' => null], $object->toArray());

        $object->one = 'again';

        $this->assertNotEmpty($object);
        $this->assertEquals('again', $object->one);
        $this->assertEquals('again', $object['one']);
        $this->assertEquals(['one' => 'again'], $object->toArray());

        $object->one = 'oncemore';

        $this->assertNotEmpty($object);
        $this->assertEquals('oncemore', $object->one);
        $this->assertEquals('oncemore', $object['one']);
        $this->assertEquals(['one' => 'oncemore'], $object->toArray());

        $object->addMetadata(['two' => null]);
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

        $this->assertEquals(['two' => null, 'one' => null], $object->toArray());

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

        $object = new MetadataArrayObject(['one' => 'two']);
        $this->assertNotEmpty($object);
        $this->assertCount(1, $object);

        $object->one = null;
        $this->assertCount(1, $object);

        $object->addMetadata(['two' => 'second']);
        $this->assertCount(2, $object);

        $object->two = null;
        $this->assertCount(2, $object);

        $object->one = 'first';
        $this->assertCount(2, $object);

        $object->addMetadata(['three' => null]);
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

        $object = new MetadataArrayObject(['one' => 'first']);
        $this->assertNotEmpty($object);
        $this->assertNotEmpty($object->toArray());
        $this->assertEquals(['one' => 'first'], $object->toArray());

        $object->populate(['two' => 'second']);
        $this->assertNotEmpty($object);
        $this->assertNotEmpty($object->toArray());
        $this->assertEquals(['two' => 'second'], $object->toArray());

        $object->populate(['third' => null]);
        $this->assertNotEmpty($object);
        $this->assertNotEmpty($object->toArray());
        $this->assertEquals(['third' => null], $object->toArray());
    }

    /**
     * @expectedException PHPUnit_Framework_Error_Notice
     */
    public function testGetKeyNonExisting()
    {
        $object = new MetadataArrayObject();
        $object['test'];
    }

    /**
     * @expectedException PHPUnit_Framework_Error_Notice
     */
    public function testGetPropertyNonExisting()
    {
        $object = new MetadataArrayObject();
        $object->test;
    }

    /**
     * @expectedException PHPUnit_Framework_Error_Notice
     */
    public function testSetKeyNonExisting()
    {
        $object = new MetadataArrayObject();
        $object['test'] = 'tessst';
    }

    /**
     * @expectedException PHPUnit_Framework_Error_Notice
     */
    public function testSetPropertyNonExisting()
    {
        $object = new MetadataArrayObject();
        $object->test = 'tessst';
    }

    /**
     * @expectedException PHPUnit_Framework_Error_Notice
     */
    public function testUnsetKeyNonExisting()
    {
        $object = new MetadataArrayObject();
        unset($object['test']);
    }

    /**
     * @expectedException PHPUnit_Framework_Error_Notice
     */
    public function testUnsetPropertyNonExisting()
    {
        $object = new MetadataArrayObject();
        unset($object->test);
    }
}

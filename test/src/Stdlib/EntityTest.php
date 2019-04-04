<?php


namespace RealejoTest\Stdlib;


use PHPUnit\Framework\TestCase;

class EntityTest extends TestCase
{
    public function testString()
    {
        $entity = new EntityConcrete();
        $this->assertObjectHasAttribute('integerValue', $entity);
    }
}
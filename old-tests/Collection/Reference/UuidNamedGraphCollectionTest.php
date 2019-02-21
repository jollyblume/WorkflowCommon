<?php

namespace JBJ\Workflow\Tests\Collection\Reference;

use JBJ\Workflow\Tests\Collection\BaseCollectionTest;
use JBJ\Workflow\Collection\Reference\UuidNamedGraphCollection;
use JBJ\Workflow\Traits\CreateIdTrait;
use JBJ\Workflow\Validator\UuidValidator;

class UuidNamedGraphCollectionTest extends BaseCollectionTest
{
    use CreateIdTrait;

    protected function createCollection(string $name, array $elements = [])
    {
        $collection = new UuidNamedGraphCollection($name, $elements);
        return $collection;
    }

    public function testEmptyName()
    {
        $collection = $this->createCollection('');
        $validator = new UuidValidator();
        $this->assertTrue($validator->validate($collection->getName()));
    }

    public function testStringName()
    {
        $collection = $this->createCollection('test.string');
        $validator = new UuidValidator();
        $this->assertTrue($validator->validate($collection->getName()));
        $otherCollection = $this->createCollection('test.string');
        $this->assertEquals($collection->getName(), $otherCollection->getName());
    }

    public function testUuidName()
    {
        $uuid = $this->createId();
        $collection = $this->createCollection($uuid);
        $this->assertEquals($uuid, $collection->getName());
    }
}

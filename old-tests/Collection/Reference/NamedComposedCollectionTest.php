<?php

namespace JBJ\Workflow\Tests\Collection\Reference;

use JBJ\Workflow\Tests\Collection\BaseCollectionTest;
use JBJ\Workflow\Collection\Reference\NamedComposedCollection;

class NamedComposedCollectionTest extends BaseCollectionTest
{
    /** @SuppressWarnings(PHPMD) */
    protected function createCollection(string $name, array $elements = [])
    {
        $collection = new NamedComposedCollection($name, $elements);
        return $collection;
    }

    public function testName()
    {
        $collection = $this->createCollection('test');
        $this->assertEquals('test', $collection->getName());
    }
}

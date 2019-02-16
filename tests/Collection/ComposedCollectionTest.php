<?php

namespace JBJ\Workflow\Tests\Collection;

use JBJ\Workflow\Collection\ArrayCollectionInterface;
use JBJ\Workflow\Collection\ComposedCollection;

class ComposedCollectionTest extends BaseCollectionTest
{
    protected function createCollection(string $name, array $elements = [])
    {
        $collection = new ComposedCollection($elements);
        return $collection;
    }
}

<?php

namespace JBJ\Workflow\Tests\Collection\Reference;

use JBJ\Workflow\Tests\Collection\BaseCollectionTest;
use JBJ\Workflow\Collection\Reference\GraphCollection;

class GraphCollectionTest extends BaseCollectionTest
{
    protected function createCollection(string $name, array $elements = [])
    {
        $collection = new GraphCollection($name, $elements);
        return $collection;
    }
}

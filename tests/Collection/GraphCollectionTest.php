<?php

namespace JBJ\Workflow\Tests\Collection;

use JBJ\Workflow\Collection\ArrayCollectionInterface;
use JBJ\Workflow\Collection\GraphCollection;

class GraphCollectionTest extends BaseCollectionTest
{
    protected function createCollection(string $name, array $elements = [])
    {
        $collection = new GraphCollection($name, $elements);
        return $collection;
    }
}

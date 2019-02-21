<?php

namespace JBJ\Workflow\Tests\Collection\Reference;

use JBJ\Workflow\Tests\Collection\BaseCollectionTest;
use JBJ\Workflow\Collection\Reference\ComposedCollection;

class ComposedCollectionTest extends BaseCollectionTest
{
    /** @SuppressWarnings(PHPMD) */
    protected function createCollection(string $name, array $elements = [])
    {
        $collection = new ComposedCollection($elements);
        return $collection;
    }
}

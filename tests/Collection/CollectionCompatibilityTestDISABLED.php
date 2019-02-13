<?php

namespace JBJ\Workflow\Tests\Collection;

use JBJ\Workflow\Collection\ComposedCollection;

class CollectionCompatibilityTest extends BaseCollectionTest
{
    public function setup()
    {
        $this->collection = new ComposedCollection();
    }
}

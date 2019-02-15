<?php

namespace JBJ\Workflow\Tests\Collection;

use JBJ\Workflow\Collection\ArrayCollectionInterface;
use JBJ\Workflow\Collection\ComposedCollection;

class ArrayCollectionCompatibilityTest extends BaseCollectionTraitTest
{
    protected function getTestClass() : string
    {
        return ComposedCollection::class;
    }
}

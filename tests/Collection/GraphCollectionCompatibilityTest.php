<?php

namespace JBJ\Workflow\Tests\Collection;

use JBJ\Workflow\Collection\ArrayCollectionInterface;
use JBJ\Workflow\Collection\GraphCollection;

class GraphCollectionCompatibilityTest extends BaseCollectionTraitTest
{
    protected function getTestClass() : string
    {
        return GraphCollection::class;
    }
}

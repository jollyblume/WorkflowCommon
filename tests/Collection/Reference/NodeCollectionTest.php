<?php

namespace JBJ\Workflow\Tests\Collection\Reference;

use JBJ\Workflow\Tests\Collection\DataProviderTrait;
use JBJ\Workflow\Collection\Reference\NodeCollection;
use PHPUnit\Framework\TestCase;

class NodeCollectionTest extends TestCase
{
    use DataProviderTrait;

    protected function getTestClassname()
    {
        return NodeCollection::class;
    }

    protected function createCollection(string $name, array $elements = [])
    {
        $collection = new NodeCollection($name, $elements);
        return $collection;
    }
}

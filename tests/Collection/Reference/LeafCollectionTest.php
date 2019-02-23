<?php

namespace JBJ\Workflow\Tests\Collection\Reference;

use JBJ\Workflow\Tests\Collection\DataProviderTrait;
use JBJ\Workflow\Collection\Reference\LeafCollection;
use PHPUnit\Framework\TestCase;

class LeafCollectionTest extends TestCase
{
    use DataProviderTrait;

    protected function getTestClassname()
    {
        return LeafCollection::class;
    }

    protected function createCollection(string $name, array $elements = [])
    {
        $collection = new LeafCollection($name, $elements);
        return $collection;
    }
}

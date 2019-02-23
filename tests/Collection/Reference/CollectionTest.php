<?php

namespace JBJ\Workflow\Tests\Collection\Reference;

use JBJ\Workflow\Tests\Collection\DataProviderTrait;
use JBJ\Workflow\Collection\Reference\Collection;
use PHPUnit\Framework\TestCase;

class CollectionTest extends TestCase
{
    use DataProviderTrait;

    protected function getTestClassname()
    {
        return Collection::class;
    }

    protected function createCollection(string $name, array $elements = [])
    {
        $name;
        $collection = new Collection($elements);
        return $collection;
    }
}

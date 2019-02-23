<?php

namespace JBJ\Workflow\Tests\Collection\Reference;

use JBJ\Workflow\Tests\Collection\DataProviderTrait;
use JBJ\Workflow\Collection\Reference\UuidLeafCollection;
use JBJ\Workflow\Validator\UuidValidator;
use PHPUnit\Framework\TestCase;

class UuidLeafCollectionTest extends TestCase
{
    use DataProviderTrait;

    protected function getTestClassname()
    {
        return UuidLeafCollection::class;
    }

    protected function createCollection(string $name, array $elements = [])
    {
        $collection = new UuidLeafCollection($name, $elements);
        return $collection;
    }
}

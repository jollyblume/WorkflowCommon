<?php

namespace JBJ\Workflow\Tests\Collection;

use JBJ\Workflow\ArrayCollectionInterface;
use JBJ\Workflow\NodeCollectionInterface;
use JBJ\Workflow\NodeInterface;
use JBJ\Workflow\Collection\CollectionTrait;
use JBJ\Workflow\Collection\LeafCollectionTrait;
use JBJ\Workflow\Collection\NodeCollectionTrait;
use PHPUnit\Framework\TestCase;

class ClonedLeafCollectionTraitTest extends TestCase
{
    use DataProviderTrait;

    protected function getTestClassname()
    {
        return get_class($this->createCollection('getTestClassname'));
    }

    protected function createCollection(string $name, array $elements = [])
    {
        $name;
        $collection = new class($elements) implements NodeCollectionInterface {
            use LeafCollectionTrait;
            public function __construct(array $elements = [])
            {
                $this->saveElements($elements);
            }
        };
        $clone = clone $collection;
        return $clone;
    }

    public function testIsLeafNodeTrue()
    {
        $collection = $this->createCollection('testIsLeafNodeTrue');
        $this->assertTrue($collection->isLeafNode());
    }

    public function testCreateAcceptableElement()
    {
        $collection = $this->createCollection('testCreateAcceptableElement');
        $element = $this->createAcceptableElement('element');
        $collection[] = $element;
        $this->assertTrue($collection->contains($element));
    }

    public function testIsCollectionFalse()
    {
        $this->assertFalse($this->isCollection());
    }

    public function testIsLeafCollectionTrue()
    {
        $this->assertTrue($this->isLeafCollection());
    }

    public function testIsNodeCollectionFalse()
    {
        $this->assertFalse($this->isNodeCollection());
    }

    public function testGetDataForTestCase()
    {
        $data = $this->getDataForTestCase()[0];
        $expectedData = array_merge($this->getNodeCompatibleData()[0], $this->getDoctrineTestData()[0]);
        $this->assertEquals($expectedData, $data);
    }

    public function testHydrateElementKeys()
    {
        $collection = $this->createCollection('testHydrateElementKeys');
        $data = $this->getDataForTestCase();
        foreach ($data as $dataIndex => $dataSet) {
            $collection->clear();
            foreach ($dataSet as $key => $value) {
                $collection->set($key, $value);
            }
            $hydrated = $this->hydrateElementKeys($dataSet);
            $this->assertEquals($hydrated, $collection->toArray(), sprintf('Dataindex "%s"', $dataIndex));
        }
    }

    /** @dataProvider getDataForTestCase */
    public function testDataProvider($elements)
    {
        $collection = $this->createCollection('testDataProvider', $elements);
        $hydrated = $this->hydrateElementKeys($elements);
        $this->assertEquals($hydrated, $collection->toArray());
        return $collection;
    }
}

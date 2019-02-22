<?php

namespace JBJ\Workflow\Tests\Collection;

use JBJ\Workflow\ArrayCollectionInterface;
use JBJ\Workflow\NodeCollectionInterface;
use JBJ\Workflow\NodeInterface;
use JBJ\Workflow\Collection\CollectionTrait;
use JBJ\Workflow\Collection\LeafCollectionTrait;
use JBJ\Workflow\Collection\NodeCollectionTrait;
use PHPUnit\Framework\TestCase;

class DataProviderTraitTest extends TestCase
{
    protected function getTraitFixture()
    {
        $trait = new class() {
            use DataProviderTrait {
                getTestClassname as public;
                setTestClassname as public;
                getTraitNames as public;
                getRelevantTraitName as public;
                createCollection as public;
                createLeafCollection as public;
                createNodeCollection as public;
                createAcceptableElement as public;
            }
        };
        return $trait;
    }

    public function testGetTestClassname()
    {
        $trait = $this->getTraitFixture();
        $this->assertNull($trait->getTestClassname());
        $this->assertNull($trait->getRelevantTraitName());
    }

    public function testSetTestClassnameCollectionWithString()
    {
        $trait = $this->getTraitFixture();
        $testClass = $trait->createCollection();
        $trait->setTestClassname(get_class($testClass));
        $this->assertEquals(get_class($testClass), $trait->getTestClassname());
        $this->assertEquals(CollectionTrait::class, $trait->getRelevantTraitName());
    }

    public function testSetTestClassnameLeafCollectionWithString()
    {
        $trait = $this->getTraitFixture();
        $testClass = $trait->createLeafCollection('leaf.collection');
        $trait->setTestClassname(get_class($testClass));
        $this->assertEquals(get_class($testClass), $trait->getTestClassname());
        $this->assertEquals(LeafCollectionTrait::class, $trait->getRelevantTraitName());
    }

    public function testSetTestClassnameNodeCollectionWithString()
    {
        $trait = $this->getTraitFixture();
        $testClass = $trait->createNodeCollection('node.collection');
        $trait->setTestClassname(get_class($testClass));
        $this->assertEquals(get_class($testClass), $trait->getTestClassname());
        $this->assertEquals(NodeCollectionTrait::class, $trait->getRelevantTraitName());
    }

    public function testSetTestClassnameWithObject()
    {
        $trait = $this->getTraitFixture();
        $testClass = $trait->createCollection();
        $trait->setTestClassname($testClass);
        $this->assertEquals(get_class($testClass), $trait->getTestClassname());
    }

    /** @expectedException \Exception */
    public function testSetTestClassnameThrowMissingClass()
    {
        $trait = $this->getTraitFixture();
        $trait->setTestClassname('not-a-class');
    }

    /** @expectedException \Exception */
    public function testSetTestClassnameThrowInvalidClass()
    {
        $trait = $this->getTraitFixture();
        $trait->setTestClassname($this);
    }

    public function testCreateCollection()
    {
        $trait = $this->getTraitFixture();
        $collection = $trait->createCollection(['test']);
        $this->assertEquals(['test'], $collection->toArray());
        $this->assertInstanceOf(ArrayCollectionInterface::class, $collection);
    }

    public function testLeafCreateCollection()
    {
        $trait = $this->getTraitFixture();
        $collection = $trait->createLeafCollection('leaf', ['test']);
        $this->assertEquals(['test'], $collection->toArray());
        $this->assertInstanceOf(ArrayCollectionInterface::class, $collection);
        $this->assertInstanceOf(NodeInterface::class, $collection);
        $this->assertEquals('leaf', $collection->getName());
    }

    public function testCreateNodeCollection()
    {
        $trait = $this->getTraitFixture();
        $leaf = $trait->createLeafCollection('leaf', ['test']);
        $collection = $trait->createNodeCollection('node', [$leaf]);
        $this->assertEquals(['leaf' => $leaf], $collection->toArray());
        $this->assertInstanceOf(ArrayCollectionInterface::class, $collection);
        $this->assertInstanceOf(NodeCollectionInterface::class, $collection);
        $this->assertInstanceOf(NodeInterface::class, $collection);
        $this->assertEquals('node', $collection->getName());
    }

    public function testCreateAcceptableElementForCollection()
    {
        $trait = $this->getTraitFixture();
        $collection = $trait->createCollection();
        $trait->setTestClassname($collection);
        $element = $trait->createAcceptableElement('element');
        $collection[] = $element;
        $this->assertTrue($collection->contains($element));
    }

    public function testCreateAcceptableElementForLeafCollection()
    {
        $trait = $this->getTraitFixture();
        $collection = $trait->createLeafCollection('leaf');
        $trait->setTestClassname($collection);
        $element = $trait->createAcceptableElement('element');
        $collection[] = $element;
        $this->assertTrue($collection->contains($element));
    }

    public function testCreateAcceptableElementForNodeCollection()
    {
        $trait = $this->getTraitFixture();
        $collection = $trait->createNodeCollection('node');
        $trait->setTestClassname($collection);
        $element = $trait->createAcceptableElement('element');
        $collection[] = $element;
        $this->assertTrue($collection->containsKey('element'));
    }
}

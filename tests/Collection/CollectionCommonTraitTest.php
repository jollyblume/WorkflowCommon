<?php

namespace JBJ\Workflow\Tests\Collection;

use Doctrine\Common\Collections\ArrayCollection;
use JBJ\Workflow\NodeInterface;
use JBJ\Workflow\Collection\CollectionCommonTrait;
use JBJ\Workflow\Collection\CollectionTrait;
use JBJ\Workflow\Collection\GraphCollectionTrait;
use PHPUnit\Framework\TestCase;

class CollectionCommonTraitTest extends BaseCollectionTest
{
    public function testGetChildren()
    {
        $collection = $this->createCollection('testGetChildren');
        $this->assertInstanceOf(ArrayCollection::class, $collection->getChildren());
        $this->assertInstanceOf(NodeInterface::class, $collection);
        $this->assertEquals([], $collection->toArray());
    }

    public function testSaveChildren()
    {
        $elements = [
            $this->createAcceptableElement('element1'),
            $this->createAcceptableElement('element2'),
        ];
        $collection = $this->createCollection('testSaveChildren', $elements);
        $this->assertEquals($elements, $collection->toArray());
    }

    protected function createCollection(string $name, array $elements = [])
    {
        $collection = new class($name, $elements) implements NodeInterface {
            use CollectionCommonTrait {
                saveElements as public;
                getChildren as public;
            }
            private $name;
            public function __construct(string $name, array $elements)
            {
                $this->setName($name);
                $this->saveElements($elements);
            }
            public function getName()
            {
                return $this->name;
            }
            public function setName(string $name)
            {
                $this->name = $name;
                return $this;
            }
            public function set($key, $value)
            {
                $this->getChildren()->set($key, $value);
                return $this;
            }
            public function add($element)
            {
                return $this->getChildren()->add($element);
            }
            public function remove($key)
            {
                return $this->getChildren()->remove($key);
            }
            public function removeElement($element)
            {
                return $this->getChildren()->removeElement($element);
            }
        };
        if (null === $this->getTestClassname()) {
            $this->setTestClassname($collection);
        }
        return $collection;
    }

    protected function createComposedCollection()
    {
        $collection = new class() {
            use CollectionTrait;
        };
        return $collection;
    }

    protected function createGraphCollection()
    {
        $collection = new class() {
            use GraphCollectionTrait;
        };
        return $collection;
    }

    protected function createArrayCollection()
    {
        $collection = new ArrayCollection();
        return $collection;
    }

    public function testIsGraphFalseForComposedCollection()
    {
        $collection = $this->createComposedCollection();
        $this->setTestClassname($collection);
        $this->assertFalse($this->isGraph());
    }

    public function testIsGraphTrueForGraphCollection()
    {
        $collection = $this->createGraphCollection();
        $this->setTestClassname($collection);
        $this->assertTrue($this->isGraph());
    }

    public function testIsGraphFalseForArrayCollection()
    {
        $collection = $this->createArrayCollection();
        $this->setTestClassname($collection);
        $this->assertFalse($this->isGraph());
    }
}

<?php

namespace JBJ\Workflow\Tests\Traits;

use JBJ\Workflow\NodeInterface;
use JBJ\Workflow\Collection\NamedCollectionTrait;
use JBJ\Workflow\Collection\GraphCollectionTrait;
use JBJ\Workflow\Traits\ExpectedClassTrait;
use PHPUnit\Framework\TestCase;

class ExpectedClassTraitTest extends TestCase
{
    protected function getCollection(string $name, array $elements = [])
    {
        $collection = new class($name, $elements) implements NodeInterface {
            use GraphCollectionTrait {
                set as innerSet;
            }
            use ExpectedClassTrait {
                getExpectedClasses as public;
                setExpectedClasses as public;
                assertExpectedClasses as public;
            }
            public function __construct(string $name, array $elements = [])
            {
                $this->setName($name);
                $this->saveElements($elements);
            }
            public function set($key, $element)
            {
                $this->assertExpectedClasses($element);
                return $this->innerSet($key, $element);
            }
        };
        return $collection;
    }

    protected function getAcceptableChild(string $name, array $elements = [])
    {
        $child = new class($name, $elements) implements NodeInterface {
            use NamedCollectionTrait;

            public function __construct(string $name, array $elements = [])
            {
                $this->setName($name);
                $this->saveElements($elements);
            }
        };
        return $child;
    }

    protected function getDifferentChild(string $name, array $elements = [])
    {
        $child = new class($name, $elements) implements NodeInterface {
            use NamedCollectionTrait;

            public function __construct(string $name, array $elements = [])
            {
                $this->setName($name);
                $this->saveElements($elements);
            }
        };
        return $child;
    }

    public function testGetDefaultEmpty()
    {
        $collection = $this->getCollection('test');
        $this->assertEquals([], $collection->getExpectedClasses());
    }

    public function testGetDefaultAcceptsAnyClass()
    {
        $collection = $this->getCollection('test');
        $collection[] = $this->getAcceptableChild('child1');
        $this->assertArrayHasKey('child1', $collection);
    }

    public function testGetSetAcceptsExpectedClass()
    {
        $collection = $this->getCollection('test');
        $child = $this->getAcceptableChild('child1');
        $collection->setExpectedClasses([get_class($child)]);
        $this->assertTrue($collection->hasExpectedClasses($child));
        $collection[] = $child;
        $this->assertArrayHasKey('child1', $collection);
    }

    /** @expectedException \JBJ\Workflow\Exception\FixMeException */
    public function testGetSetThrowsUnexpectedClass()
    {
        $collection = $this->getCollection('test');
        $child = $this->getAcceptableChild('child1');
        $child2 = $this->getDifferentChild('child2');
        $collection->setExpectedClasses([get_class($child2)]);
        $this->assertTrue($collection->hasExpectedClasses($child2));
        $this->assertFalse($collection->hasExpectedClasses($child));
        $collection[] = $child;
    }

    public function testAssertOk()
    {
        $collection = $this->getCollection('test');
        $child = $this->getAcceptableChild('child1');
        $collection->setExpectedClasses([get_class($child)]);
        $collection->assertExpectedClasses($child);
        $collection[] = $child;
        $this->assertArrayHasKey('child1', $collection);
    }

    /** @expectedException \JBJ\Workflow\Exception\FixMeException */
    public function testAssertThrows()
    {
        $collection = $this->getCollection('test');
        $child = $this->getAcceptableChild('child1');
        $collection->setExpectedClasses([get_class($child)]);
        $child2 = $this->getDifferentChild('child2');
        $collection->assertExpectedClasses($child2);
    }
}

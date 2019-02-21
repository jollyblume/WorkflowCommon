<?php

namespace JBJ\Workflow\Tests\Collection;

use JBJ\Workflow\NodeInterface;
use JBJ\Workflow\Collection\CollectionTrait;
use PHPUnit\Framework\TestCase;

class CollectionCloneTest extends TestCase
{
    protected function createCollection(array $elements = [])
    {
        $collection = new class($elements) implements NodeInterface {
            use CollectionTrait;
            public function __construct(array $elements = [])
            {
                $this->saveElements($elements);
            }
        };
        return $collection;
    }

    public function testBasicClone()
    {
        $collection = $this->createCollection(['thing1', 'thing2']);
        $clone = clone $collection;
        $collection[] = 'thing-original';
        $clone[] = 'thing-clone';
        $expextedCollection = [
            'thing1',
            'thing2',
            'thing-original',
        ];
        $expectedClone = [
            'thing1',
            'thing2',
            'thing-clone',
        ];
        $this->assertEquals($expextedCollection, $collection->toArray());
        $this->assertEquals($expectedClone, $clone->toArray());
    }

    public function testReletiveValuesDoNotChange()
    {
        $referencedClass = new class() {
        };
        $elements = [
            'class1' => $referencedClass,
            'class2' => $referencedClass,
        ];
        $collection = $this->createCollection($elements);
        $clone = clone $collection;
        $this->assertEquals($referencedClass, $clone['class1']);
        $this->assertEquals($referencedClass, $clone['class2']);
    }
}

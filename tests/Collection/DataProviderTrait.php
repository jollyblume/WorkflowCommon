<?php

namespace JBJ\Workflow\Tests\Collection;

use Doctrine\Common\Collections\Criteria;
use JBJ\Workflow\ArrayCollectionInterface;
use JBJ\Workflow\NodeCollectionInterface;
use JBJ\Workflow\Collection\CollectionTrait;
use JBJ\Workflow\Collection\LeafCollectionTrait;
use JBJ\Workflow\Collection\NodeCollectionTrait;
use Ramsey\Uuid\Uuid;
use PHPUnit\Framework\TestCase;

trait DataProviderTrait
{
    abstract protected function getTestClassname();
    abstract protected function createCollection(string $name, array $elements = []);

    protected function class_uses_deep($class, $autoload = true)
    {
        $traits = [];

        // Get traits of all parent classes
        do {
            $traits = array_merge(class_uses($class, $autoload), $traits);
        } while ($class = get_parent_class($class));

        // Get traits of all parent traits
        $traitsToSearch = $traits;
        while (!empty($traitsToSearch)) {
            $newTraits = class_uses(array_pop($traitsToSearch), $autoload);
            $traits = array_merge($newTraits, $traits);
            $traitsToSearch = array_merge($newTraits, $traitsToSearch);
        };

        foreach ($traits as $trait => $same) {
            $traits = array_merge(class_uses($trait, $autoload), $traits);
        }

        return array_unique($traits);
    }

    private $relevantTraitName;
    protected function getRelevantTraitName()
    {
        $relevantName = $this->relevantTraitName;
        if (!$relevantName) {
            $relevantNamesFixture = [
                NodeCollectionTrait::class,
                LeafCollectionTrait::class,
                CollectionTrait::class,
            ];
            $classname = $this->getTestClassname();
            $traitNames = $this->class_uses_deep($classname);
            foreach ($relevantNamesFixture as $relevantName) {
                if (array_key_exists($relevantName, $traitNames)) {
                    break;
                }
            }
            if (!$relevantName) {
                throw new \Exception(sprintf('No relative names found in "%s"', join(',', $traitNames)));
            }
        }
        return $relevantName;
    }

    protected function createCollectionElement(array $elements = [])
    {
        $collection = new class($elements) implements ArrayCollectionInterface {
            use CollectionTrait;
            public function __construct(array $elements = [])
            {
                $this->saveElements($elements);
            }
            private $otherValue;
            public function getOtherValue()
            {
                return $this->otherValue;
            }
            public function setOtherValue($otherValue)
            {
                $this->otherValue = $otherValue;
            }
        };
        return $collection;
    }

    protected function createLeafCollectionElement(string $name, array $elements = [])
    {
        $collection = new class($name, $elements) implements NodeCollectionInterface {
            use LeafCollectionTrait;
            public function __construct(string $name, array $elements = [])
            {
                $this->setName($name);
                $this->saveElements($elements);
            }
            private $otherValue;
            public function getOtherValue()
            {
                return $this->otherValue;
            }
            public function setOtherValue($otherValue)
            {
                $this->otherValue = $otherValue;
            }
        };
        return $collection;
    }

    protected function createNodeCollectionElement(string $name, array $elements = [])
    {
        $collection = new class($name, $elements) implements NodeCollectionInterface {
            use NodeCollectionTrait;
            public function __construct(string $name, array $elements = [])
            {
                $this->setName($name);
                $this->saveElements($elements);
            }
            private $otherValue;
            public function getOtherValue()
            {
                return $this->otherValue;
            }
            public function setOtherValue($otherValue)
            {
                $this->otherValue = $otherValue;
            }
        };
        return $collection;
    }

    protected function createAcceptableElement(string $name, array $elements = [])
    {
        $allowedNames = [
            NodeCollectionTrait::class => 'createLeafCollectionElement',
            LeafCollectionTrait::class => 'createCollectionElement',
            CollectionTrait::class => 'createCollectionElement',
        ];
        $relevantName = $this->getRelevantTraitName();
        $method = $allowedNames[$relevantName];
        if ($this->isNodeCollection()) {
            return $this->$method($name, $elements);
        }
        return $this->$method($elements);
    }

    protected function isCollection()
    {
        $isNodeCollection = $this->getRelevantTraitName() === CollectionTrait::class;
        return $isNodeCollection;
    }

    protected function isLeafCollection()
    {
        $isLeafCollection = $this->getRelevantTraitName() === LeafCollectionTrait::class;
        return $isLeafCollection;
    }

    protected function isNodeCollection()
    {
        $isNodeCollection = $this->getRelevantTraitName() === NodeCollectionTrait::class;
        return $isNodeCollection;
    }

    public function getDoctrineTestData()
    {
        return [[
            'indexed'     => [1, 2, 3, 4, 5],
            'associative' => ['A' => 'a', 'B' => 'b', 'C' => 'c'],
            'mixed'       => ['A' => 'a', 1, 'B' => 'b', 2, 3],
        ]];
    }

    public function getNodeCompatibleData()
    {
        return [[
            'indexed-graph' => [
                $this->createAcceptableElement('test.id.1'),
                $this->createAcceptableElement('test.id.2'),
                $this->createAcceptableElement('test.id.3'),
                $this->createAcceptableElement('test.id.4'),
                $this->createAcceptableElement('test.id.5'),
                ],
            'associative-graph' => [
                'test.id.aA' => $this->createAcceptableElement('test.id.aA'),
                'test.id.aB' => $this->createAcceptableElement('test.id.aB'),
                'test.id.aC' => $this->createAcceptableElement('test.id.aC'),
                'test.id.aD' => $this->createAcceptableElement('test.id.aD'),
                ],
            'mixed-graph' => [
                'test.id.bA' => $this->createAcceptableElement('test.id.bA'),
                $this->createAcceptableElement('test.id.6'),
                'test.id.bB' => $this->createAcceptableElement('test.id.bB'),
                $this->createAcceptableElement('test.id.7'),
                $this->createAcceptableElement('test.id.8'),
                ],
        ]];
    }

    public function getDataForTestCase()
    {
        $data = $this->getNodeCompatibleData()[0];
        if (!$this->isNodeCollection()) {
            $data = array_merge($data, $this->getDoctrineTestData()[0]);
        }
        return [$data];
    }

    protected function hydrateElementKeys($elements)
    {
        if (!$this->isNodeCollection() || empty($elements)) {
            return (array) $elements;
        }
        $hydrated = [];
        foreach ($elements as $key => $element) {
            $key = $element->getName();
            $hydrated[$key] = $element;
        }
        return $hydrated;
    }

    /*********************** ArrayCollection Tests below *********************/

    /** @dataProvider getDataForTestCase */
    public function testFirst($elements)
    {
        $collection = $this->createCollection('test.collection', $elements);
        $elements = $this->hydrateElementKeys($elements);
        $this->assertSame(reset($elements), $collection->first());
    }

    /** @dataProvider getDataForTestCase */
    public function testLast($elements)
    {
        $collection = $this->createCollection('test.collection', $elements);
        $elements = $this->hydrateElementKeys($elements);
        $this->assertSame(end($elements), $collection->last());
    }

    /** @dataProvider getDataForTestCase */
    public function testNext($elements)
    {
        $collection = $this->createCollection('test.collection', $elements);
        $elements = $this->hydrateElementKeys($elements);
        while (true) {
            $collectionNext = $collection->next();
            $arrayNext      = next($elements);

            if (! $collectionNext || ! $arrayNext) {
                break;
            }

            $this->assertSame($arrayNext, $collectionNext, 'Returned value of ArrayCollection::next() and next() not match');
            $this->assertSame(key($elements), $collection->key(), 'Keys not match');
            $this->assertSame(current($elements), $collection->current(), 'Current values not match');
        }
    }

    /** @dataProvider getDataForTestCase */
    public function testKey($elements)
    {
        $collection = $this->createCollection('test.collection', $elements);
        $elements = $this->hydrateElementKeys($elements);
        $this->assertSame(key($elements), $collection->key());
        while (true) {
            $collectionNext = $collection->next();
            $arrayNext      = next($elements);

            if (! $collectionNext || ! $arrayNext) {
                break;
            }

            $this->assertSame(key($elements), $collection->key());
        }
    }

    /** @dataProvider getDataForTestCase */
    public function testCurrent($elements)
    {
        $collection = $this->createCollection('test.collection', $elements);
        $elements = $this->hydrateElementKeys($elements);
        $this->assertSame(current($elements), $collection->current());
        while (true) {
            $collectionNext = $collection->next();
            $arrayNext      = next($elements);

            if (! $collectionNext || ! $arrayNext) {
                break;
            }

            $this->assertSame(current($elements), $collection->current());
        }
    }

    /** @dataProvider getDataForTestCase */
    public function testGetKeys($elements)
    {
        $collection = $this->createCollection('test.collection', $elements);
        $elements = $this->hydrateElementKeys($elements);
        $this->assertSame(array_keys($elements), $collection->getKeys());
    }

    /** @dataProvider getDataForTestCase */
    public function testGetValues($elements)
    {
        $collection = $this->createCollection('test.collection', $elements);
        // $elements = $this->hydrateElementKeys($elements);
        $this->assertSame(array_values($elements), $collection->getValues());
    }

    /** @dataProvider getDataForTestCase */
    public function testCount($elements)
    {
        $collection = $this->createCollection('test.collection', $elements);
        // $elements = $this->hydrateElementKeys($elements);
        $this->assertSame(count($elements), $collection->count());
    }

    /** @dataProvider getDataForTestCase */
    public function testIterator($elements)
    {
        $collection = $this->createCollection('test.collection', $elements);
        $elements = $this->hydrateElementKeys($elements);
        $iterations = 0;
        foreach ($collection->getIterator() as $key => $item) {
            $this->assertSame($elements[$key], $item);
            ++$iterations;
        }

        $this->assertEquals(count($elements), $iterations, 'Number of iterations not match');
    }

    /** @dataProvider getDataForTestCase */
    public function testEmpty($elements)
    {
        $collection = $this->createCollection('test.collection');
        $this->assertTrue($collection->isEmpty(), 'Empty collection');
        foreach ($elements as $key => $value) {
            $collection[$key] = $value;
        }
        $this->assertFalse($collection->isEmpty());
        $this->assertCount(count($elements), $collection);
    }

    /** @dataProvider getDataForTestCase */
    public function testRemove($elements)
    {
        // actual test follows:
        $collection = $this->createCollection('test.collection', $elements);
        $elements = $this->hydrateElementKeys($elements);
        $this->assertNull($collection->remove('test.id.zZ'));
        foreach ($elements as $key => $value) {
            $this->assertEquals($value, $collection->remove($key));
        }
        $this->assertTrue($collection->isEmpty());
    }

    /** @dataProvider getDataForTestCase */
    public function testRemoveElement($elements)
    {
        $collection = $this->createCollection('test.collection', $elements);
        $elements = $this->hydrateElementKeys($elements);
        $missingElement = $this->createAcceptableElement('test.id.zZ');
        $this->assertFalse($collection->removeElement($missingElement));
        foreach ($elements as $value) {
            $this->assertTrue($collection->removeElement($value));
        }
        $this->assertTrue($collection->isEmpty());
    }

    /** @dataProvider getDataForTestCase */
    public function testContainsKey($elements)
    {
        $collection = $this->createCollection('test.collection', $elements);
        $elements = $this->hydrateElementKeys($elements);
        $this->assertFalse($collection->containsKey('test.id.zZ'));
        foreach (array_keys($elements) as $value) {
            $this->assertTrue($collection->containsKey($value));
        }
    }

    /** @dataProvider getDataForTestCase */
    public function testContains($elements)
    {
        $collection = $this->createCollection('test.collection', $elements);
        // $elements = $this->hydrateElementKeys($elements);
        $missingElement = $this->createAcceptableElement('test.id.zZ');
        $this->assertFalse($collection->contains($missingElement));
        foreach ($elements as $value) {
            $this->assertTrue($collection->contains($value));
        }
    }

    /** @dataProvider getDataForTestCase */
    public function testExists($elements)
    {
        // actual test follows:
        $collection = $this->createCollection('test.collection', $elements);
        $elements = $this->hydrateElementKeys($elements);
        $this->assertFalse(isset($collection['test.id.zZ']));
        foreach (array_keys($elements) as$value) {
            $this->assertTrue(isset($collection[$value]));
        }
    }

    /** @dataProvider getDataForTestCase */
    public function testIndexOf($elements)
    {
        $collection = $this->createCollection('test.collection', $elements);
        $elements = $this->hydrateElementKeys($elements);
        $this->assertFalse($collection->indexOf('test.id.zZ'));
        foreach ($elements as $key => $value) {
            $this->assertEquals($key, $collection->indexOf($value));
        }
    }

    /** @dataProvider getDataForTestCase */
    public function testGet($elements)
    {
        $collection = $this->createCollection('test.collection', $elements);
        $elements = $this->hydrateElementKeys($elements);
        $this->assertNull($collection['test.id.zZ']);
        foreach ($elements as $key => $value) {
            $this->assertEquals($value, $collection[$key]);
        }
    }

    /** @dataProvider getDataForTestCase */
    public function testToString($elements)
    {
        $collection = $this->createCollection('test.collection', $elements);
        $this->assertTrue(is_string((string) $collection));
    }

    /** @dataProvider getDataForTestCase */
    public function testIssetAndUnset($elements)
    {
        $collection = $this->createCollection('test.collection', $elements);
        $elements = $this->hydrateElementKeys($elements);
        foreach (array_keys($elements) as $value) {
            $this->assertTrue(isset($collection[$value]));
            unset($collection[$value]);
            $this->assertFalse(isset($collection[$value]));
        }
    }

    /** @dataProvider getDataForTestCase */
    public function testClear($elements)
    {
        $collection = $this->createCollection('test.collection', $elements);
        $this->assertTrue(0 < $collection->count());
        $collection->clear();
        $this->assertEquals([], $collection->toArray());
    }

    /** @dataProvider getDataForTestCase */
    public function testSlice($elements)
    {
        $collection = $this->createCollection('test.collection', $elements);
        $expectedValue = $collection->last();
        $value = $collection->slice(-1, 1);
        $this->assertEquals($expectedValue, array_shift($value));
    }

    /** @dataProvider getDataForTestCase */
    public function testPartition($elements)
    {
        $collection = $this->createCollection('test.collection', $elements);
        $expectedPassed = [];
        $expectedFailed = [];
        foreach ($collection as $key => $value) {
            if (is_string($key)) {
                $expectedPassed[$key] = $value;
            }
            if (!is_string($key)) {
                $expectedFailed[$key] = $value;
            }
        }
        $predicate = function ($key, $value) {
            return is_string($key);
        };
        list($passed, $failed) = $collection->partition($predicate);
        $this->assertEquals($expectedPassed, $passed->toArray());
        $this->assertEquals($expectedFailed, $failed->toArray());
    }

    /**
    * @dataProvider getNodeCompatibleData
    */
    public function testMatchingWithSortingPreservesKeys($elements)
    {
        $collection = $this->createCollection('test.collection', $elements);
        $elements = $this->hydrateElementKeys($elements);
        $sortMap = [];
        foreach ($collection as $key => $value) {
            $sortOrder = strval(Uuid::uuid4());
            $value->setOtherValue($sortOrder);
            $sortMap[$key] = $sortOrder;
        }
        $sortSuccessful = asort($sortMap);
        if (!$sortSuccessful) {
            throw new \JBJ\Workflow\Exception\FixMeException('sort failed');
        }
        $matched = $collection
            ->matching(new Criteria(null, ['otherValue' => Criteria::ASC]))
            ->toArray();
        $actual = [];
        foreach ($matched as $key => $value) {
            $actual[$key] = $value->getOtherValue();
        }
        $this->assertEquals($sortMap, $actual);
    }
}

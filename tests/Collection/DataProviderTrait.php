<?php

namespace JBJ\Workflow\Tests\Collection;

use JBJ\Workflow\ArrayCollectionInterface;
use JBJ\Workflow\NodeCollectionInterface;
use JBJ\Workflow\NodeInterface;
use JBJ\Workflow\Collection\CollectionTrait;
use JBJ\Workflow\Collection\LeafCollectionTrait;
use JBJ\Workflow\Collection\NodeCollectionTrait;
use PHPUnit\Framework\TestCase;

trait DataProviderTrait
{
    abstract protected function getTestClassname();
    abstract protected function createCollection(string $name, array $elements = []);

    public function testAcceptableDataProvider()
    {
        $data = $this->getDataForTestCase();
        $count = $this->isNodeCollection() ? 3 : 6;
        $this->assertEquals(count($data), $count);
        return [$data];
    }

    public function testNodeDataProvider()
    {
        $data = $this->getNodeCompatibleData();
        $this->assertCount(3, $data);
        return [$data];
    }

    public function testDoctrineDataProvider()
    {
        $data = $this->getDoctrineTestData();
        $this->assertCount(3, $data);
        return [$data];
    }

    protected function getTraitNames($class)
    {
        $classname = is_object($class) ? get_class($class) : strval($class);
        $rClass = new \ReflectionClass($classname);
        $parents = [$rClass];
        while ($parent = $rClass->getParentClass()) {
            $parents[] = $parent;
            $rClass = $parent;
        }
        $traitNames = [];
        foreach ($parents as $rClass) {
            $traitNames = array_merge($traitNames, $rClass->getTraitNames());
        }
        //todo do i care about traits used by traits? probably not
        return $traitNames;
    }

    private $relevantTraitName;
    protected function getRelevantTraitName()
    {
        $relevantName = $this->relevantTraitName;
        if (!$relevantName) {
            $relevantNamesFixture = [
                CollectionTrait::class,
                LeafCollectionTrait::class,
                NodeCollectionTrait::class,
            ];
            $classname = $this->getTestClassname();
            $traitNames = $this->getTraitNames($classname);
            $relevantNames = array_intersect($relevantNamesFixture, $traitNames);
            if (count($relevantNames) === 0) {
                throw new \Exception(sprintf('No relative names found in "%s"', join(',', $traitNames)));
            }
            $relevantName = reset($relevantNames);
            $this->relevantTraitName = $relevantName;
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

    protected function getDoctrineTestData()
    {
        return [
            'indexed'     => [1, 2, 3, 4, 5],
            'associative' => ['A' => 'a', 'B' => 'b', 'C' => 'c'],
            'mixed'       => ['A' => 'a', 1, 'B' => 'b', 2, 3],
        ];
    }

    protected function getNodeCompatibleData()
    {
        return [
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
        ];
    }

    protected function getDataForTestCase()
    {
        $data = $this->getNodeCompatibleData();
        if (!$this->isNodeCollection()) {
            $data = array_merge($data, $this->getDoctrineTestData());
        }
        return $data;
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

    /** @dataProvider testAcceptableDataProvider */
    public function testFirst($elements)
    {
        $collection = $this->createCollection('test.collection', $elements);
        $elements = $this->hydrateElementKeys($elements);
        $this->assertSame(reset($elements), $collection->first());
    }

    /** @dataProvider testAcceptableDataProvider */
    public function testLast($elements)
    {
        $collection = $this->createCollection('test.collection', $elements);
        $elements = $this->hydrateElementKeys($elements);
        $this->assertSame(end($elements), $collection->last());
    }

    /** @dataProvider testAcceptableDataProvider */
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

    /** @dataProvider testAcceptableDataProvider */
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

    /** @dataProvider testAcceptableDataProvider */
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

    /** @dataProvider testAcceptableDataProvider */
    public function testGetKeys($elements)
    {
        $collection = $this->createCollection('test.collection', $elements);
        $elements = $this->hydrateElementKeys($elements);
        $this->assertSame(array_keys($elements), $collection->getKeys());
    }

    /** @dataProvider testAcceptableDataProvider */
    public function testGetValues($elements)
    {
        $collection = $this->createCollection('test.collection', $elements);
        // $elements = $this->hydrateElementKeys($elements);
        $this->assertSame(array_values($elements), $collection->getValues());
    }

    /** @dataProvider testAcceptableDataProvider */
    public function testCount($elements)
    {
        $collection = $this->createCollection('test.collection', $elements);
        // $elements = $this->hydrateElementKeys($elements);
        $this->assertSame(count($elements), $collection->count());
    }

    /** @dataProvider testAcceptableDataProvider */
    public function testIterator($elements)
    {
        $collection = $this->createCollection('test.collection', $elements);
        $elements = $this->hydrateElementKeys($elements);
        $iterations = 0;
        foreach ($collection->getIterator() as $key => $item) {
            $this->assertSame($elements[$key], $item, 'Item ' . $key . ' not match');
            ++$iterations;
        }

        $this->assertEquals(count($elements), $iterations, 'Number of iterations not match');
    }

    /** @dataProvider testAcceptableDataProvider */
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

    /** @dataProvider testAcceptableDataProvider */
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

    /** @dataProvider testAcceptableDataProvider */
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

    /** @dataProvider testAcceptableDataProvider */
    public function testContainsKey($elements)
    {
        $collection = $this->createCollection('test.collection', $elements);
        $elements = $this->hydrateElementKeys($elements);
        $this->assertFalse($collection->containsKey('test.id.zZ'));
        foreach (array_keys($elements) as $value) {
            $this->assertTrue($collection->containsKey($value));
        }
    }

    /** @dataProvider testAcceptableDataProvider */
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

    /** @dataProvider testAcceptableDataProvider */
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

    /** @dataProvider testAcceptableDataProvider */
    public function testIndexOf($elements)
    {
        $collection = $this->createCollection('test.collection', $elements);
        $elements = $this->hydrateElementKeys($elements);
        $this->assertFalse($collection->indexOf('test.id.zZ'));
        foreach ($elements as $key => $value) {
            $this->assertEquals($key, $collection->indexOf($value));
        }
    }

    /** @dataProvider testAcceptableDataProvider */
    public function testGet($elements)
    {
        $collection = $this->createCollection('test.collection', $elements);
        $elements = $this->hydrateElementKeys($elements);
        $this->assertNull($collection['test.id.zZ']);
        foreach ($elements as $key => $value) {
            $this->assertEquals($value, $collection[$key]);
        }
    }

    /** @dataProvider testAcceptableDataProvider */
    public function testToString($elements)
    {
        $collection = $this->createCollection('test.collection', $elements);
        $this->assertTrue(is_string((string) $collection));
    }

    /** @dataProvider testAcceptableDataProvider */
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

    /** @dataProvider testAcceptableDataProvider */
    public function testClear($elements)
    {
        $collection = $this->createCollection('test.collection', $elements);
        $this->assertTrue(0 < $collection->count());
        $collection->clear();
        $this->assertEquals([], $collection->toArray());
    }

    /** @dataProvider testAcceptableDataProvider */
    public function testSlice($elements)
    {
        $collection = $this->createCollection('test.collection', $elements);
        $expectedValue = $collection->last();
        $value = $collection->slice(-1, 1);
        $this->assertEquals($expectedValue, array_shift($value));
    }

    /** @dataProvider testAcceptableDataProvider */
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

    // /**
    // * @depends testGraphDataProvider
    // * @SuppressWarnings(PHPMD.StaticAccess)
    // */
    // public function testMatchingWithSortingPreservesKeys($elements)
    // {
    //     $collection = $this->createCollection('test.collection', $elements);
    //     $elements = $this->hydrateElementKeys($elements);
    //
    //     $sortMap = [];
    //     foreach ($collection as $key => $value) {
    //         $sortOrder = strval(Uuid::uuid4());
    //         $value->setOtherValue($sortOrder);
    //         $sortMap[$key] = $sortOrder;
    //     }
    //     $sortSuccessful = asort($sortMap);
    //     if (!$sortSuccessful) {
    //         throw new \JBJ\Workflow\Exception\FixMeException('sort failed');
    //     }
    //     $matched = $collection
    //         ->matching(new Criteria(null, ['otherValue' => Criteria::ASC]))
    //         ->toArray();
    //     $actual = [];
    //     foreach ($matched as $key => $value) {
    //         $actual[$key] = $value->getOtherValue();
    //     }
    //     $this->assertEquals($sortMap, $actual, $datasetIndex);
    // }
}

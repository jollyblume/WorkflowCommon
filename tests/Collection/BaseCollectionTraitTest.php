<?php

namespace JBJ\Workflow\Tests\Collection;

use Doctrine\Common\Collections\Criteria;
use Doctrine\Common\Collections\Selectable;
use Doctrine\Common\Collections\ArrayCollection;
use JBJ\Workflow\Collection\ArrayCollectionInterface;
use Ramsey\Uuid\Uuid;
use PHPUnit\Framework\TestCase;

abstract class BaseCollectionTraitTest extends TestCase
{
    abstract protected function getTestClass();

    private $isGraph; // don't use this directly
    protected function isGraph()
    {
        $isGraph = $this->isGraph;
        if (null === $isGraph) {
            $rClass = new \ReflectionClass($this->getTestClass());
            $parents = [$rClass];
            while ($parent = $rClass->getParentClass()) {
                $parents[] = $parent;
                $rClass = $parent;
            }
            $traitNames = [];
            foreach ($parents as $rClass) {
                $traitNames = array_merge($traitNames, $rClass->getTraitNames());
            }
            $collectionTraits = [];
            foreach (['JBJ\Workflow\Collection\CollectionTrait', 'JBJ\Workflow\Collection\GraphCollectionTrait'] as $collectionTrait) {
                if (in_array($collectionTrait, $traitNames, true)) {
                    $collectionTraits[] = $collectionTrait;
                }
            }
            if (empty($collectionTraits)) {
                throw new \Exception(sprintf('No collection traits found for "%s in "[%s]"', $classname, join(',', $traitNames)));
            }
            if (1 < count($collectionTraits)) {
                throw new \Exception(sprintf('Too many collection traits found for "%s in "%s"', $classname, join(',', $traitNames)));
            }
            $isGraph = $collectionTraits[0] === 'JBJ\Workflow\Collection\GraphCollectionTrait';
            $this->isGraph = $isGraph;
        }
        return $isGraph;
    }

    protected function createCollection(string $name, array $elements = []) : ArrayCollectionInterface
    {
        $testClass = $this->getTestClass();
        $collection = $this->isGraph() ? new $testClass($name, $elements) : new $testClass($elements);
        return $collection;
    }

    protected function createAcceptableElement(string $key)
    {
        $element = new class($key) {
            private $name;
            private $parent;
            private $otherValue;
            public function __construct(string $name)
            {
                $this->name = $name;
            }
            public function getName()
            {
                return $this->name;
            }
            public function getParent()
            {
                return $this->parent;
            }
            public function setParent(
                $parent
            ) {
                $this->parent = $parent;
            }
            public function getOtherValue()
            {
                return $this->otherValue;
            }
            public function setOtherValue($otherValue)
            {
                $this->otherValue = $otherValue;
            }
            public function __toString()
            {
                return $this->getName();
            }
        };
        return $element;
    }

    private function getDoctrineTestData()
    {
        return [
            'indexed'     => [1, 2, 3, 4, 5],
            'associative' => ['A' => 'a', 'B' => 'b', 'C' => 'c'],
            'mixed'       => ['A' => 'a', 1, 'B' => 'b', 2, 3],
        ];
    }

    protected function getGraphCompatibleData()
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

    protected function buildDataProvider()
    {
        $isGraph = $this->isGraph();
        $doctrineTestData = $this->getDoctrineTestData();
        $graphTestData = $this->getGraphCompatibleData();
        $providers = array_merge($doctrineTestData, $graphTestData);
        $providerData = [];
        foreach ($providers as $datasetIndex => $dataset) {
            $graphIndex = false !== strpos($datasetIndex, '-graph');
            if ($isGraph && !$graphIndex) {
                continue;
            }
            $providerData[$datasetIndex] = $dataset;
        }
        $providerData['__DATAPROVIDER__'] = true;
        return $providerData;
    }

    protected function getNextDataset(string $method, $providerData)
    {
        if (array_key_exists('__DATASETINDEX__', $providerData)) {
            return $providerData;
        }
        if (array_key_exists('__DATAPROVIDER__', $providerData)) {
            foreach ($providerData as $datasetIndex => $dataset) {
                if ('__DATAPROVIDER__' === $datasetIndex) {
                    continue;
                }
                $dataset['__DATASETINDEX__'] = $datasetIndex;
                $this->$method($dataset); //run test with dataset
            }
            return null; //signal no more datasets
        }
        throw new \Exception('Unknown providerData');
    }

    public function testExpectedInterfaces()
    {
        $collection = $this->createCollection('test.collection');
        $this->assertInstanceOf(ArrayCollectionInterface::class, $collection);
        $testClass = $this->getTestClass();
        $this->assertInstanceOf($testClass, $collection);
    }

    /** This is the data provider entry point */
    public function testDataProvider()
    {
        $providerData = $this->buildDataProvider();
        $expectedCount = $this->isGraph() ? 3 : 6;
        $expectedCount++; // for $providerData['__DATAPROVIDER__']
        $this->assertEquals($expectedCount, count($providerData));
        return $providerData;
    }

    public function testCreateAcceptableElement()
    {
        $element = $this->createAcceptableElement('test.key');
        if ($this->isGraph()) {
            $this->assertTrue(method_exists($element, 'getName'));
            $this->assertTrue(method_exists($element, 'getParent'));
            $this->assertTrue(method_exists($element, 'setParent'));
        }
        $this->assertTrue(method_exists($element, 'getOtherValue'));
        $this->assertTrue(method_exists($element, 'setOtherValue'));
        $this->assertTrue(method_exists($element, '__toString'));
    }

    public function testCreateCollection()
    {
        $elements = [
            $this->createAcceptableElement('test.element.1'),
            $this->createAcceptableElement('test.element.2'),
            $this->createAcceptableElement('test.element.3'),
        ];
        $collection = $this->createCollection('test.collection', $elements);
        $testClass = $this->getTestClass();
        $this->assertInstanceOf($testClass, $collection);
        $expectedElements = $elements;
        if ($this->isGraph()) {
            $expectedElements = [
                'test.element.1' => $elements[0],
                'test.element.2' => $elements[1],
                'test.element.3' => $elements[2],
            ];
        }
        $this->assertEquals($expectedElements, $collection->toArray());
    }

    protected function hydrateElementKeys($elements)
    {
        if (!$this->isGraph() || empty($elements)) {
            return $elements;
        }
        $hydrated = [];
        foreach ($elements as $key => $element) {
            if (!is_string($key)) {
                $key = $element->getName();
            }
            $hydrated[$key] = $element;
        }
        return $hydrated;
    }

    /** @depends testDataProvider */
    public function testHydrateElementKeysUsingConstructor($providerData)
    {
        $elements = $this->getNextDataset(__FUNCTION__, $providerData);
        if (null === $elements) {
            return;
        }
        if (array_key_exists('__DATASETINDEX__', $elements)) {
            $datasetIndex = $elements['__DATASETINDEX__'];
            unset($elements['__DATASETINDEX__']);
        }

        // actual test follows:
        $collection = $this->createCollection($datasetIndex, $elements);
        $expectedElements = $this->hydrateElementKeys($elements);
        $this->assertEquals($expectedElements, $collection->toArray());
    }

    /** @depends testDataProvider */
    public function testHydrateElementKeysUsingSet($providerData)
    {
        $elements = $this->getNextDataset(__FUNCTION__, $providerData);
        if (null === $elements) {
            return;
        }
        if (array_key_exists('__DATASETINDEX__', $elements)) {
            $datasetIndex = $elements['__DATASETINDEX__'];
            unset($elements['__DATASETINDEX__']);
        }

        // actual test follows:
        $collection = $this->createCollection($datasetIndex);
        foreach ($elements as $key => $element) {
            $collection[$key] = $element;
        }
        $expectedElements = $this->hydrateElementKeys($elements);
        $this->assertEquals($expectedElements, $collection->toArray());
    }

    /** @depends testDataProvider */
    public function testHydrateElementKeysUsingAdd($providerData)
    {
        $elements = $this->getNextDataset(__FUNCTION__, $providerData);
        if (null === $elements) {
            return;
        }
        if (array_key_exists('__DATASETINDEX__', $elements)) {
            $datasetIndex = $elements['__DATASETINDEX__'];
            unset($elements['__DATASETINDEX__']);
        }

        // actual test follows:
        if (false === strpos($datasetIndex, 'indexed')) {
            return;
        }
        $collection = $this->createCollection($datasetIndex);
        foreach ($elements as $element) {
            $collection[] = $element;
        }
        $expectedElements = $this->hydrateElementKeys($elements);
        $this->assertEquals($expectedElements, $collection->toArray());
    }

    /** @depends testDataProvider */
    public function testElementParents($providerData)
    {
        if (!$this->isGraph()) {
            $this->markTestSkipped('Test not applicable to composite collections');
        }

        $elements = $this->getNextDataset(__FUNCTION__, $providerData);
        if (null === $elements) {
            return;
        }
        if (array_key_exists('__DATASETINDEX__', $elements)) {
            $datasetIndex = $elements['__DATASETINDEX__'];
            unset($elements['__DATASETINDEX__']);
        }

        // actual test follows:
        $collection = $this->createCollection($datasetIndex, $elements);
        foreach ($collection as $element) {
            $parent = $element->getParent();
            $this->assertEquals($collection, $parent);
        }
    }

    /*********************** Array Tests below *********************/

    /** @depends testDataProvider */
    public function testFirst($providerData)
    {
        $elements = $this->getNextDataset(__FUNCTION__, $providerData);
        if (null === $elements) {
            return;
        }
        if (array_key_exists('__DATASETINDEX__', $elements)) {
            $datasetIndex = $elements['__DATASETINDEX__'];
            unset($elements['__DATASETINDEX__']);
        }

        // actual test follows:
        $collection = $this->createCollection($datasetIndex, $elements);
        $elements = $this->hydrateElementKeys($elements);
        $this->assertSame(reset($elements), $collection->first());
    }

    /** @depends testDataProvider */
    public function testLast($providerData)
    {
        $elements = $this->getNextDataset(__FUNCTION__, $providerData);
        if (null === $elements) {
            return;
        }
        if (array_key_exists('__DATASETINDEX__', $elements)) {
            $datasetIndex = $elements['__DATASETINDEX__'];
            unset($elements['__DATASETINDEX__']);
        }

        // actual test follows:
        $collection = $this->createCollection($datasetIndex, $elements);
        $elements = $this->hydrateElementKeys($elements);
        $this->assertSame(end($elements), $collection->last());
    }

    /** @depends testDataProvider */
    public function testNext($providerData)
    {
        $elements = $this->getNextDataset(__FUNCTION__, $providerData);
        if (null === $elements) {
            return;
        }
        if (array_key_exists('__DATASETINDEX__', $elements)) {
            $datasetIndex = $elements['__DATASETINDEX__'];
            unset($elements['__DATASETINDEX__']);
        }

        // actual test follows:
        $collection = $this->createCollection($datasetIndex, $elements);
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

    /** @depends testDataProvider */
    public function testKey($providerData)
    {
        $elements = $this->getNextDataset(__FUNCTION__, $providerData);
        if (null === $elements) {
            return;
        }
        if (array_key_exists('__DATASETINDEX__', $elements)) {
            $datasetIndex = $elements['__DATASETINDEX__'];
            unset($elements['__DATASETINDEX__']);
        }

        // actual test follows:
        $collection = $this->createCollection($datasetIndex, $elements);
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

    /** @depends testDataProvider */
    public function testCurrent($providerData)
    {
        $elements = $this->getNextDataset(__FUNCTION__, $providerData);
        if (null === $elements) {
            return;
        }
        if (array_key_exists('__DATASETINDEX__', $elements)) {
            $datasetIndex = $elements['__DATASETINDEX__'];
            unset($elements['__DATASETINDEX__']);
        }

        // actual test follows:
        $collection = $this->createCollection($datasetIndex, $elements);
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

    /** @depends testDataProvider */
    public function testGetKeys($providerData)
    {
        $elements = $this->getNextDataset(__FUNCTION__, $providerData);
        if (null === $elements) {
            return;
        }
        if (array_key_exists('__DATASETINDEX__', $elements)) {
            $datasetIndex = $elements['__DATASETINDEX__'];
            unset($elements['__DATASETINDEX__']);
        }

        // actual test follows:
        $collection = $this->createCollection($datasetIndex, $elements);
        $elements = $this->hydrateElementKeys($elements);
        $this->assertSame(array_keys($elements), $collection->getKeys());
    }

    /** @depends testDataProvider */
    public function testGetValues($providerData)
    {
        $elements = $this->getNextDataset(__FUNCTION__, $providerData);
        if (null === $elements) {
            return;
        }
        if (array_key_exists('__DATASETINDEX__', $elements)) {
            $datasetIndex = $elements['__DATASETINDEX__'];
            unset($elements['__DATASETINDEX__']);
        }

        // actual test follows:
        $collection = $this->createCollection($datasetIndex, $elements);
        // $elements = $this->hydrateElementKeys($elements);
        $this->assertSame(array_values($elements), $collection->getValues());
    }

    /** @depends testDataProvider */
    public function testCount($providerData)
    {
        $elements = $this->getNextDataset(__FUNCTION__, $providerData);
        if (null === $elements) {
            return;
        }
        if (array_key_exists('__DATASETINDEX__', $elements)) {
            $datasetIndex = $elements['__DATASETINDEX__'];
            unset($elements['__DATASETINDEX__']);
        }

        // actual test follows:
        $collection = $this->createCollection($datasetIndex, $elements);
        // $elements = $this->hydrateElementKeys($elements);
        $this->assertSame(count($elements), $collection->count());
    }

    // /** @depends testDataProvider */
    // public function testIterator($providerData)
    // {
    //     $elements = $this->getNextDataset(__FUNCTION__, $providerData);
    //     if (null === $elements) {
    //         return;
    //     }
    //     if (array_key_exists('__DATASETINDEX__', $elements)) {
    //         $datasetIndex = $elements['__DATASETINDEX__'];
    //         unset($elements['__DATASETINDEX__']);
    //     }
    //
    //     // actual test follows:
    //     $collection = $this->createCollection($datasetIndex, $elements);
    //     $elements = $this->hydrateElementKeys($elements);
    //     $iterations = 0;
    //     foreach ($collection->getIterator() as $key => $item) {
    //         $this->assertSame($elements[$key], $item, 'Item ' . $key . ' not match');
    //         ++$iterations;
    //     }
    //
    //     $this->assertEquals(count($elements), $iterations, 'Number of iterations not match');
    // }
    //
    // /** @depends testDataProvider */
    // public function testEmpty($providerData)
    // {
    //     $elements = $this->getNextDataset(__FUNCTION__, $providerData);
    //     if (null === $elements) {
    //         return;
    //     }
    //     if (array_key_exists('__DATASETINDEX__', $elements)) {
    //         $datasetIndex = $elements['__DATASETINDEX__'];
    //         unset($elements['__DATASETINDEX__']);
    //     }
    //
    //     // actual test follows:
    //     $collection = $this->createCollection($datasetIndex);
    //     $this->assertTrue($collection->isEmpty(), 'Empty collection');
    //     foreach ($elements as $key => $value) {
    //         $collection[$key] = $value;
    //     }
    //     $this->assertFalse($collection->isEmpty(), 'Not empty collection for "' . $datasetIndex . '"."');
    //     $this->assertCount(count($elements), $collection, 'Wrong collection count for "' . $datasetIndex . '"."');
    // }
    //
    // /** @depends testDataProvider */
    // public function testRemove($providerData)
    // {
    //     $elements = $this->getNextDataset(__FUNCTION__, $providerData);
    //     if (null === $elements) {
    //         return;
    //     }
    //     if (array_key_exists('__DATASETINDEX__', $elements)) {
    //         $datasetIndex = $elements['__DATASETINDEX__'];
    //         unset($elements['__DATASETINDEX__']);
    //     }
    //
    //     // actual test follows:
    //     $missingElement = $this->createAcceptableElement('test.id.zZ');
    //     $collection = $this->createCollection($datasetIndex, $elements);
    //     $elements = $this->hydrateElementKeys($elements);
    //     $this->assertNull($collection->remove($missingElement->getName()));
    //     foreach ($elements as $key => $value) {
    //         $this->assertEquals($value, $collection->remove($key));
    //     }
    //     $this->assertTrue($collection->isEmpty(), 'Empty collection for "' . $datasetIndex . '"."');
    // }
    //
    // /** @depends testDataProvider */
    // public function testRemoveElement($providerData)
    // {
    //     $elements = $this->getNextDataset(__FUNCTION__, $providerData);
    //     if (null === $elements) {
    //         return;
    //     }
    //     if (array_key_exists('__DATASETINDEX__', $elements)) {
    //         $datasetIndex = $elements['__DATASETINDEX__'];
    //         unset($elements['__DATASETINDEX__']);
    //     }
    //
    //     // actual test follows:
    //     $missingElement = $this->createAcceptableElement('test.id.zZ');
    //     $collection = $this->createCollection($datasetIndex, $elements);
    //     $elements = $this->hydrateElementKeys($elements);
    //     $this->assertFalse($collection->removeElement($missingElement));
    //     foreach ($elements as $value) {
    //         $this->assertTrue($collection->removeElement($value));
    //     }
    //     $this->assertTrue($collection->isEmpty(), 'Empty collection for "' . $datasetIndex . '"."');
    // }
    //
    // /** @depends testDataProvider */
    // public function testContainsKey($providerData)
    // {
    //     $elements = $this->getNextDataset(__FUNCTION__, $providerData);
    //     if (null === $elements) {
    //         return;
    //     }
    //     if (array_key_exists('__DATASETINDEX__', $elements)) {
    //         $datasetIndex = $elements['__DATASETINDEX__'];
    //         unset($elements['__DATASETINDEX__']);
    //     }
    //
    //     // actual test follows:
    //     $missingElement = $this->createAcceptableElement('test.id.zZ');
    //     $collection = $this->createCollection($datasetIndex, $elements);
    //     $elements = $this->hydrateElementKeys($elements);
    //     $this->assertFalse($collection->containsKey($missingElement->getName(), 'Missing key found for "' . $datasetIndex . '"."'));
    //     foreach (array_keys($elements) as $value) {
    //         $this->assertTrue($collection->containsKey($value), 'Expected key not found for "' . $datasetIndex . '"."');
    //     }
    // }
    //
    // /** @depends testDataProvider */
    // public function testContains($providerData)
    // {
    //     $elements = $this->getNextDataset(__FUNCTION__, $providerData);
    //     if (null === $elements) {
    //         return;
    //     }
    //     if (array_key_exists('__DATASETINDEX__', $elements)) {
    //         $datasetIndex = $elements['__DATASETINDEX__'];
    //         unset($elements['__DATASETINDEX__']);
    //     }
    //
    //     // actual test follows:
    //     $missingElement = $this->createAcceptableElement('test.id.zZ');
    //     $collection = $this->createCollection($datasetIndex, $elements);
    //     // $elements = $this->hydrateElementKeys($elements);
    //     $this->assertFalse($collection->contains($missingElement), 'Missing element found for "' . $datasetIndex . '"."');
    //     foreach ($elements as $value) {
    //         $this->assertTrue($collection->contains($value), 'Expected element not found for "' . $datasetIndex . '"."');
    //     }
    // }
    //
    // /** @depends testDataProvider */
    // public function testExists($providerData)
    // {
    //     $elements = $this->getNextDataset(__FUNCTION__, $providerData);
    //     if (null === $elements) {
    //         return;
    //     }
    //     if (array_key_exists('__DATASETINDEX__', $elements)) {
    //         $datasetIndex = $elements['__DATASETINDEX__'];
    //         unset($elements['__DATASETINDEX__']);
    //     }
    //
    //     // actual test follows:
    //     $missingElement = $this->createAcceptableElement('test.id.zZ');
    //     $collection = $this->createCollection($datasetIndex, $elements);
    //     $elements = $this->hydrateElementKeys($elements);
    //     $this->assertFalse(isset($collection[$missingElement->getName()]), 'Missing key set for "' . $datasetIndex . '"."');
    //     foreach (array_keys($elements) as$value) {
    //         $this->assertTrue(isset($collection[$value]), 'Expected key not set for "' . $datasetIndex . '".');
    //     }
    // }
    //
    // /** @depends testDataProvider */
    // public function testIndexOf($providerData)
    // {
    //     $elements = $this->getNextDataset(__FUNCTION__, $providerData);
    //     if (null === $elements) {
    //         return;
    //     }
    //     if (array_key_exists('__DATASETINDEX__', $elements)) {
    //         $datasetIndex = $elements['__DATASETINDEX__'];
    //         unset($elements['__DATASETINDEX__']);
    //     }
    //
    //     // actual test follows:
    //     $missingElement = $this->createAcceptableElement('test.id.zZ');
    //     $collection = $this->createCollection($datasetIndex, $elements);
    //     $elements = $this->hydrateElementKeys($elements);
    //     $this->assertFalse($collection->indexOf($missingElement->getName()), 'Index of missing key found for "' . $datasetIndex . '"."');
    //     foreach ($elements as $key => $value) {
    //         $this->assertEquals($key, $collection->indexOf($value), 'Index of expected key not found for "' . $datasetIndex . '"."');
    //     }
    // }
    //
    // /** @depends testDataProvider */
    // public function testGet($providerData)
    // {
    //     $elements = $this->getNextDataset(__FUNCTION__, $providerData);
    //     if (null === $elements) {
    //         return;
    //     }
    //     if (array_key_exists('__DATASETINDEX__', $elements)) {
    //         $datasetIndex = $elements['__DATASETINDEX__'];
    //         unset($elements['__DATASETINDEX__']);
    //     }
    //
    //     // actual test follows:
    //     $missingElement = $this->createAcceptableElement('test.id.zZ');
    //     $collection = $this->createCollection($datasetIndex, $elements);
    //     $elements = $this->hydrateElementKeys($elements);
    //     $this->assertNull($collection[$missingElement->getName()], 'Missing element returned for "' . $datasetIndex . '"."');
    //     foreach ($elements as $key => $value) {
    //         $this->assertEquals($value, $collection[$key], 'Expected element not returned for "' . $datasetIndex . '"."');
    //     }
    // }
    //
    // /** @depends testDataProvider */
    // public function testToString($providerData)
    // {
    //     $elements = $this->getNextDataset(__FUNCTION__, $providerData);
    //     if (null === $elements) {
    //         return;
    //     }
    //     if (array_key_exists('__DATASETINDEX__', $elements)) {
    //         $datasetIndex = $elements['__DATASETINDEX__'];
    //         unset($elements['__DATASETINDEX__']);
    //     }
    //
    //     // actual test follows:
    //     $collection = $this->createCollection($datasetIndex, $elements);
    //     $this->assertTrue(is_string((string) $collection));
    // }
    //
    // /** @depends testDataProvider */
    // public function testIssetAndUnset($providerData)
    // {
    //     $elements = $this->getNextDataset(__FUNCTION__, $providerData);
    //     if (null === $elements) {
    //         return;
    //     }
    //     if (array_key_exists('__DATASETINDEX__', $elements)) {
    //         $datasetIndex = $elements['__DATASETINDEX__'];
    //         unset($elements['__DATASETINDEX__']);
    //     }
    //
    //     // actual test follows:
    //     $collection = $this->createCollection($datasetIndex, $elements);
    //     $elements = $this->hydrateElementKeys($elements);
    //     foreach (array_keys($elements) as $value) {
    //         $this->assertTrue(isset($collection[$value]));
    //         unset($collection[$value]);
    //         $this->assertFalse(isset($collection[$value]));
    //     }
    // }
    //
    // /**
    //  * @depends testDataProvider
    //  * @SuppressWarnings(PHPMD.StaticAccess)
    // */
    // public function testMatchingWithSortingPreservesKeys($providerData)
    // {
    //     $elements = $this->getNextDataset(__FUNCTION__, $providerData);
    //     if (null === $elements) {
    //         return;
    //     }
    //     if (array_key_exists('__DATASETINDEX__', $elements)) {
    //         $datasetIndex = $elements['__DATASETINDEX__'];
    //         unset($elements['__DATASETINDEX__']);
    //     }
    //     if (false === strpos($datasetIndex, '-graph')) {
    //         return;
    //     }
    //
    //     // actual test follows:
    //     $collection = $this->createCollection($datasetIndex, $elements);
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
    //
    // /** @depends testDataProvider */
    // public function testClear($providerData)
    // {
    //     $elements = $this->getNextDataset(__FUNCTION__, $providerData);
    //     if (null === $elements) {
    //         return;
    //     }
    //     if (array_key_exists('__DATASETINDEX__', $elements)) {
    //         $datasetIndex = $elements['__DATASETINDEX__'];
    //         unset($elements['__DATASETINDEX__']);
    //     }
    //
    //     // actual test follows:
    //     $collection = $this->createCollection($datasetIndex, $elements);
    //     $collection->clear();
    //     $this->assertEquals([], $collection->toArray());
    // }
    //
    // /** @depends testDataProvider */
    // public function testSlice($providerData)
    // {
    //     $elements = $this->getNextDataset(__FUNCTION__, $providerData);
    //     if (null === $elements) {
    //         return;
    //     }
    //     if (array_key_exists('__DATASETINDEX__', $elements)) {
    //         $datasetIndex = $elements['__DATASETINDEX__'];
    //         unset($elements['__DATASETINDEX__']);
    //     }
    //
    //     // actual test follows:
    //     $collection = $this->createCollection($datasetIndex, $elements);
    //     $expectedValue = $collection->last();
    //     $value = $collection->slice(-1, 1);
    //
    //     $this->assertEquals($expectedValue, array_shift($value));
    // }
    //
    // /** @depends testDataProvider */
    // public function testPartition($providerData)
    // {
    //     $elements = $this->getNextDataset(__FUNCTION__, $providerData);
    //     if (null === $elements) {
    //         return;
    //     }
    //     if (array_key_exists('__DATASETINDEX__', $elements)) {
    //         $datasetIndex = $elements['__DATASETINDEX__'];
    //         unset($elements['__DATASETINDEX__']);
    //     }
    //
    //     // actual test follows:
    //     $collection = $this->createCollection($datasetIndex, $elements);
    //     $expectedPassed = [];
    //     $expectedFailed = [];
    //     foreach ($collection as $key => $value) {
    //         if (is_string($key)) {
    //             $expectedPassed[$key] = $value;
    //         }
    //         if (!is_string($key)) {
    //             $expectedFailed[$key] = $value;
    //         }
    //     }
    //     $predicate = function ($key, $value) {
    //         return is_string($key);
    //     };
    //     list($passed, $failed) = $collection->partition($predicate);
    //     $this->assertEquals($expectedPassed, $passed->toArray());
    //     $this->assertEquals($expectedFailed, $failed->toArray());
    // }
}

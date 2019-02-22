<?php

namespace JBJ\Workflow\Tests\Collection;

use Closure;
use Doctrine\Common\Collections\Criteria;
use JBJ\Workflow\NodeInterface;
use JBJ\Workflow\NodeCollectionInterface;
use JBJ\Workflow\NamedCollectionInterface;
use JBJ\Workflow\ArrayCollectionInterface;
use JBJ\Workflow\Collection\NodeCollectionTrait;
use JBJ\Workflow\Collection\NamedCollectionTrait;
use JBJ\Workflow\Collection\CollectionTrait;
use Ramsey\Uuid\Uuid;
use PHPUnit\Framework\TestCase;

abstract class BaseCollectionTest extends TestCase
{
    abstract protected function createCollection(string $name, array $elements = []);




    /** @group init */
    public function testCreateCollection()
    {
        $elements = [
            $this->createAcceptableElement('test.element.1'),
            $this->createAcceptableElement('test.element.2'),
            $this->createAcceptableElement('test.element.3'),
        ];
        $collection = $this->createCollection('test.collection', $elements);
        $this->assertInstanceOf(ArrayCollectionInterface::class, $collection);
        $this->setTestClassname($collection);
        $expectedElements = $elements;
        if ($this->isGraph()) {
            $this->assertInstanceOf(NodeCollectionInterface::class, $collection);
            $expectedElements = [
                'test.element.1' => $elements[0],
                'test.element.2' => $elements[1],
                'test.element.3' => $elements[2],
            ];
        }
        $this->assertEquals($expectedElements, $collection->toArray());
    }

    /** * @group init * This is the complete data provider */
    public function testCompleteDataProvider()
    {
        $providerData = $this->buildCompleteDataProvider();
        $this->assertEquals('complete', $providerData['__DATAPROVIDER__']);
        return $providerData;
    }

    /** * @group init * This is the context data provider */
    public function testContextDataProvider()
    {
        if ($this->isGraph()) {
            $providerData = $this->buildGraphDataProvider();
            $this->assertEquals('graph', $providerData['__DATAPROVIDER__']);
        }
        if (!$this->isGraph()) {
            $providerData = $this->buildCompleteDataProvider();
            $this->assertEquals('complete', $providerData['__DATAPROVIDER__']);
        }
        return $providerData;
    }

    /*********************** ArrayCollection Tests below *********************/

    /** @depends testContextDataProvider */
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

    /** @depends testContextDataProvider */
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

    /** @depends testContextDataProvider */
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

    /** @depends testContextDataProvider */
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

    /** @depends testContextDataProvider */
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

    /** @depends testContextDataProvider */
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

    /** @depends testContextDataProvider */
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

    /** @depends testContextDataProvider */
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

    /** @depends testContextDataProvider */
    public function testIterator($providerData)
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
        $iterations = 0;
        foreach ($collection->getIterator() as $key => $item) {
            $this->assertSame($elements[$key], $item, 'Item ' . $key . ' not match');
            ++$iterations;
        }

        $this->assertEquals(count($elements), $iterations, 'Number of iterations not match');
    }

    /** @depends testContextDataProvider */
    public function testEmpty($providerData)
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
        $this->assertTrue($collection->isEmpty(), 'Empty collection');
        foreach ($elements as $key => $value) {
            $collection[$key] = $value;
        }
        $this->assertFalse($collection->isEmpty(), 'Not empty collection for "' . $datasetIndex . '"."');
        $this->assertCount(count($elements), $collection, 'Wrong collection count for "' . $datasetIndex . '"."');
    }

    /** @depends testContextDataProvider */
    public function testRemove($providerData)
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
        $missingElement = $this->createAcceptableElement('test.id.zZ');
        $collection = $this->createCollection($datasetIndex, $elements);
        $elements = $this->hydrateElementKeys($elements);
        $this->assertNull($collection->remove($missingElement->getName()));
        foreach ($elements as $key => $value) {
            $this->assertEquals($value, $collection->remove($key));
        }
        $this->assertTrue($collection->isEmpty(), 'Empty collection for "' . $datasetIndex . '"."');
    }

    /** @depends testContextDataProvider */
    public function testRemoveElement($providerData)
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
        $missingElement = $this->createAcceptableElement('test.id.zZ');
        $collection = $this->createCollection($datasetIndex, $elements);
        $elements = $this->hydrateElementKeys($elements);
        $this->assertFalse($collection->removeElement($missingElement));
        foreach ($elements as $value) {
            $this->assertTrue($collection->removeElement($value));
        }
        $this->assertTrue($collection->isEmpty(), 'Empty collection for "' . $datasetIndex . '"."');
    }

    /** @depends testContextDataProvider */
    public function testContainsKey($providerData)
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
        $missingElement = $this->createAcceptableElement('test.id.zZ');
        $collection = $this->createCollection($datasetIndex, $elements);
        $elements = $this->hydrateElementKeys($elements);
        $this->assertFalse($collection->containsKey($missingElement->getName(), 'Missing key found for "' . $datasetIndex . '"."'));
        foreach (array_keys($elements) as $value) {
            $this->assertTrue($collection->containsKey($value), 'Expected key not found for "' . $datasetIndex . '"."');
        }
    }

    /** @depends testContextDataProvider */
    public function testContains($providerData)
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
        $missingElement = $this->createAcceptableElement('test.id.zZ');
        $collection = $this->createCollection($datasetIndex, $elements);
        // $elements = $this->hydrateElementKeys($elements);
        $this->assertFalse($collection->contains($missingElement), 'Missing element found for "' . $datasetIndex . '"."');
        foreach ($elements as $value) {
            $this->assertTrue($collection->contains($value), 'Expected element not found for "' . $datasetIndex . '"."');
        }
    }

    /** @depends testContextDataProvider */
    public function testExists($providerData)
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
        $missingElement = $this->createAcceptableElement('test.id.zZ');
        $collection = $this->createCollection($datasetIndex, $elements);
        $elements = $this->hydrateElementKeys($elements);
        $this->assertFalse(isset($collection[$missingElement->getName()]), 'Missing key set for "' . $datasetIndex . '"."');
        foreach (array_keys($elements) as$value) {
            $this->assertTrue(isset($collection[$value]), 'Expected key not set for "' . $datasetIndex . '".');
        }
    }

    /** @depends testContextDataProvider */
    public function testIndexOf($providerData)
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
        $missingElement = $this->createAcceptableElement('test.id.zZ');
        $collection = $this->createCollection($datasetIndex, $elements);
        $elements = $this->hydrateElementKeys($elements);
        $this->assertFalse($collection->indexOf($missingElement->getName()), 'Index of missing key found for "' . $datasetIndex . '"."');
        foreach ($elements as $key => $value) {
            $this->assertEquals($key, $collection->indexOf($value), 'Index of expected key not found for "' . $datasetIndex . '"."');
        }
    }

    /** @depends testContextDataProvider */
    public function testGet($providerData)
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
        $missingElement = $this->createAcceptableElement('test.id.zZ');
        $collection = $this->createCollection($datasetIndex, $elements);
        $elements = $this->hydrateElementKeys($elements);
        $this->assertNull($collection[$missingElement->getName()], 'Missing element returned for "' . $datasetIndex . '"."');
        foreach ($elements as $key => $value) {
            $this->assertEquals($value, $collection[$key], 'Expected element not returned for "' . $datasetIndex . '"."');
        }
    }

    /** @depends testContextDataProvider */
    public function testToString($providerData)
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
        $this->assertTrue(is_string((string) $collection));
    }

    /** @depends testContextDataProvider */
    public function testIssetAndUnset($providerData)
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
        foreach (array_keys($elements) as $value) {
            $this->assertTrue(isset($collection[$value]));
            unset($collection[$value]);
            $this->assertFalse(isset($collection[$value]));
        }
    }

    /** @depends testContextDataProvider */
    public function testClear($providerData)
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
        $collection->clear();
        $this->assertEquals([], $collection->toArray());
    }

    /** @depends testContextDataProvider */
    public function testSlice($providerData)
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
        $expectedValue = $collection->last();
        $value = $collection->slice(-1, 1);

        $this->assertEquals($expectedValue, array_shift($value));
    }

    /** @depends testContextDataProvider */
    public function testPartition($providerData)
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
    * @depends testGraphDataProvider
    * @SuppressWarnings(PHPMD.StaticAccess)
    */
    public function testMatchingWithSortingPreservesKeys($providerData)
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
        $this->assertEquals($sortMap, $actual, $datasetIndex);
    }
}

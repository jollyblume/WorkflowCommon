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
    use DataProviderTrait;

    public function testDataProvider()
    {
        $dataProvider = $this->getDataProvider();
        $this->assertArrayHasKey('__DATAPROVIDER__', $dataProvider);
        return $dataProvider;
    }

    /*********************** ArrayCollection Tests below *********************/

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
        $collection = $this->createTestCollection($datasetIndex, $elements);
        $elements = $this->hydrateElementKeys($elements);
        $this->assertSame(reset($elements), $collection->first());
    }

    // /** @depends testDataProvider */
    // public function testLast($providerData)
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
    //     $collection = $this->createTestCollection($datasetIndex, $elements);
    //     $elements = $this->hydrateElementKeys($elements);
    //     $this->assertSame(end($elements), $collection->last());
    // }
    //
    // /** @depends testDataProvider */
    // public function testNext($providerData)
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
    //     $collection = $this->createTestCollection($datasetIndex, $elements);
    //     $elements = $this->hydrateElementKeys($elements);
    //     while (true) {
    //         $collectionNext = $collection->next();
    //         $arrayNext      = next($elements);
    //
    //         if (! $collectionNext || ! $arrayNext) {
    //             break;
    //         }
    //
    //         $this->assertSame($arrayNext, $collectionNext, 'Returned value of ArrayCollection::next() and next() not match');
    //         $this->assertSame(key($elements), $collection->key(), 'Keys not match');
    //         $this->assertSame(current($elements), $collection->current(), 'Current values not match');
    //     }
    // }
    //
    // /** @depends testDataProvider */
    // public function testKey($providerData)
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
    //     $collection = $this->createTestCollection($datasetIndex, $elements);
    //     $elements = $this->hydrateElementKeys($elements);
    //     $this->assertSame(key($elements), $collection->key());
    //     while (true) {
    //         $collectionNext = $collection->next();
    //         $arrayNext      = next($elements);
    //
    //         if (! $collectionNext || ! $arrayNext) {
    //             break;
    //         }
    //
    //         $this->assertSame(key($elements), $collection->key());
    //     }
    // }
    //
    // /** @depends testDataProvider */
    // public function testCurrent($providerData)
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
    //     $collection = $this->createTestCollection($datasetIndex, $elements);
    //     $elements = $this->hydrateElementKeys($elements);
    //     $this->assertSame(current($elements), $collection->current());
    //     while (true) {
    //         $collectionNext = $collection->next();
    //         $arrayNext      = next($elements);
    //
    //         if (! $collectionNext || ! $arrayNext) {
    //             break;
    //         }
    //
    //         $this->assertSame(current($elements), $collection->current());
    //     }
    // }
    //
    // /** @depends testDataProvider */
    // public function testGetKeys($providerData)
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
    //     $collection = $this->createTestCollection($datasetIndex, $elements);
    //     $elements = $this->hydrateElementKeys($elements);
    //     $this->assertSame(array_keys($elements), $collection->getKeys());
    // }
    //
    // /** @depends testDataProvider */
    // public function testGetValues($providerData)
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
    //     $collection = $this->createTestCollection($datasetIndex, $elements);
    //     // $elements = $this->hydrateElementKeys($elements);
    //     $this->assertSame(array_values($elements), $collection->getValues());
    // }
    //
    // /** @depends testDataProvider */
    // public function testCount($providerData)
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
    //     $collection = $this->createTestCollection($datasetIndex, $elements);
    //     // $elements = $this->hydrateElementKeys($elements);
    //     $this->assertSame(count($elements), $collection->count());
    // }
    //
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
    //     $collection = $this->createTestCollection($datasetIndex, $elements);
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
    //     $collection = $this->createTestCollection($datasetIndex);
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
    //     $collection = $this->createTestCollection($datasetIndex, $elements);
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
    //     $collection = $this->createTestCollection($datasetIndex, $elements);
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
    //     $collection = $this->createTestCollection($datasetIndex, $elements);
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
    //     $collection = $this->createTestCollection($datasetIndex, $elements);
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
    //     $collection = $this->createTestCollection($datasetIndex, $elements);
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
    //     $collection = $this->createTestCollection($datasetIndex, $elements);
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
    //     $collection = $this->createTestCollection($datasetIndex, $elements);
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
    //     $collection = $this->createTestCollection($datasetIndex, $elements);
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
    //     $collection = $this->createTestCollection($datasetIndex, $elements);
    //     $elements = $this->hydrateElementKeys($elements);
    //     foreach (array_keys($elements) as $value) {
    //         $this->assertTrue(isset($collection[$value]));
    //         unset($collection[$value]);
    //         $this->assertFalse(isset($collection[$value]));
    //     }
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
    //     $collection = $this->createTestCollection($datasetIndex, $elements);
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
    //     $collection = $this->createTestCollection($datasetIndex, $elements);
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
    //     $collection = $this->createTestCollection($datasetIndex, $elements);
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
    //
    // /**
    // * @depends testDataProvider
    // * @SuppressWarnings(PHPMD.StaticAccess)
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
    //
    //     // actual test follows:
    //     $collection = $this->createTestCollection($datasetIndex, $elements);
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
    //     ->matching(new Criteria(null, ['otherValue' => Criteria::ASC]))
    //     ->toArray();
    //     $actual = [];
    //     foreach ($matched as $key => $value) {
    //         $actual[$key] = $value->getOtherValue();
    //     }
    //     $this->assertEquals($sortMap, $actual, $datasetIndex);
    // }
}

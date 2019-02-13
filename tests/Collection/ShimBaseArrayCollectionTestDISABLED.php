<?php

namespace JBJ\Workflow\Tests\Collection;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Criteria;
use Doctrine\Common\Collections\Selectable;
use PHPUnit\Framework\TestCase;
use JBJ\Workflow\Collection\ArrayCollectionInterface;
use JBJ\Workflow\Collection\ClassnameMetadata;
use JBJ\Workflow\Traits\PropertyAccessorTrait;
use Ramsey\Uuid\Uuid;

/**
 * NOTE:
 * This test uses a home-spun data provider implementation to avoid the
 * phpunit version, since it requires data provider initialization.
 */
abstract class ShimBaseArrayCollectionTest extends TestCase
{
    use PropertyAccessorTrait;

    /**********************
     * Data provider instrumentation
     *********************/

    private $testClass;
    private $isGraph;
    private $metadata = null;
    private $strictNames = false;
    private $strictParents = false;

    protected function initCollectionBuilder(string $testClass)
    {
        $this->testClass = $testClass;
        $collectionTrait = $this->getCollectionTrait($testClass);
        $this->isGraph = 'JBJ\Workflow\Collection\GraphCollectionTrait' === $collectionTrait;
    }

    protected function initDataProvider($metadata = null, bool $strictNames = false, bool $strictParents = false)
    {
        $this->metadata = $metadata;
        $this->strictNames = $strictNames;
        $this->strictParents = $strictParents;
    }

    protected function getCollectionTrait(string $classname)
    {
        $rc = new \ReflectionClass($classname);
        $parents = [$rc];
        while ($parent = $rc->getParentClass()) {
            $parents[] = $parent;
            $rc = $parent;
        }
        $traitNames = [];
        foreach ($parents as $rc) {
            $traitNames = array_merge($traitNames, $rc->getTraitNames());
        }
        $collectionTraits = [];
        foreach ([
                'JBJ\Workflow\Collection\CollectionTrait',
                'JBJ\Workflow\Collection\GraphCollectionTrait',
            ] as $collectionTrait) {
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
        return $collectionTraits[0];
    }

    private function assertBuilderInitialized()
    {
        $isGraph = $this->isGraph;
        $testClass = $this->testClass;
        if (null === $isGraph || null === $testClass) {
            throw new \Exception('Collection builder not initialized');
        }
    }

    protected function isGraph()
    {
        $this->assertBuilderInitialized();
        $isGraph = $this->isGraph;
        return boolval($isGraph);
    }

    protected function getTestClass()
    {
        $this->assertBuilderInitialized();
        $testClass = $this->testClass;
        return $testClass;
    }

    protected function isStrictNames()
    {
        $this->assertBuilderInitialized();
        $strictNames = $this->strictNames;
        return boolval($strictNames);
    }

    protected function isStrictParents()
    {
        $this->assertBuilderInitialized();
        $strictParents = $this->strictParents;
        return boolval($strictParents);
    }

    protected function getMetadata()
    {
        $this->assertBuilderInitialized();
        $metadata = $this->metadata;
        if (!$metadata instanceof ArrayCollection) {
            if (null === $metadata) {
                $metadata = [];
            }
            $metadata = new ArrayCollection($metadata);
            $this->metadata = $metadata;
        }
        return $metadata;
    }

    protected function getNameMetadata()
    {
        $metadata = $this->getMetadata()['name'];
        return $metadata;
    }

    protected function getNameConstraints()
    {
        $metadata = $this->getNameMetadata();
        if (!is_array($metadata)) {
            return null;
        }
        return new ClassnameMetadata('name', $metadata);
    }

    protected function getParentMetadata()
    {
        $metadata = $this->getMetadata()['parent'];
        return $metadata;
    }

    protected function getParentConstraints()
    {
        $metadata = $this->getParentMetadata();
        if (!is_array($metadata)) {
            return null;
        }
        return new ClassnameMetadata('parent', $metadata);
    }

    protected function hasNameConstraints()
    {
        $metadata = $this->getNameMetadata();
        return is_array($metadata);
    }

    protected function hasParentConstraints()
    {
        $metadata = $this->getParentMetadata();
        return is_array($metadata);
    }

    /**********************
     * Data provider fixtures
     *********************/

    private function getDoctrineTestData()
    {
        return [
            'indexed'     => [1, 2, 3, 4, 5],
            'associative' => ['A' => 'a', 'B' => 'b', 'C' => 'c'],
            'mixed'       => ['A' => 'a', 1, 'B' => 'b', 2, 3],
        ];
    }

    private function getGraphCompatibleData()
    {
        return [
            'indexed-strict' => [
                $this->buildAcceptableElement('test.id.1'),
                $this->buildAcceptableElement('test.id.2'),
                $this->buildAcceptableElement('test.id.3'),
                $this->buildAcceptableElement('test.id.4'),
                $this->buildAcceptableElement('test.id.5'),
                ],
            'associative-strict' => [
                'test.id.aA' => $this->buildAcceptableElement('test.id.aA'),
                'test.id.aB' => $this->buildAcceptableElement('test.id.aB'),
                'test.id.aC' => $this->buildAcceptableElement('test.id.aC'),
                'test.id.aD' => $this->buildAcceptableElement('test.id.aD'),
                ],
            'mixed-strict' => [
                'test.id.bA' => $this->buildAcceptableElement('test.id.bA'),
                $this->buildAcceptableElement('test.id.6'),
                'test.id.bB' => $this->buildAcceptableElement('test.id.bB'),
                $this->buildAcceptableElement('test.id.7'),
                $this->buildAcceptableElement('test.id.8'),
                ],
        ];
    }

    /**********************
     * Data provider
     *********************/

    protected function buildAcceptableElement(string $key)
    {
        $element = new class($key) {
            private $key;
            private $parent;
            private $otherValue;
            public function __construct(string $key)
            {
                $this->key = $key;
            }
            public function getName()
            {
                return $this->key;
            }
            public function getParent()
            {
                return $this->parent;
            }
            public function setParent(?ArrayCollectionInterface $parent)
            {
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
        };
        return $element;
    }

    protected function createCollection(string $testClass, array $elements = [])
    {
        $metadata = $this->getMetadata()->toArray();
        $isStrictNames = $this->isStrictNames();
        $isStrictParents = $this->isStrictParents();
        $collection = new $testClass($elements, $metadata, $isStrictNames, $isStrictParents);
        return $collection;
    }

    protected function buildCollection(array $elements = []) : ArrayCollectionInterface
    {
        $isGraph = $this->isGraph();
        $testClass = $this->getTestClass();
        if (!$isGraph) {
            $collection = new $testClass($elements);
        }
        if ($isGraph) {
            $collection = $this->createCollection($testClass, $elements);
        }
        return $collection;
    }

    protected function buildDataProvider()
    {
        $isStrict = $this->isStrictNames() || $this->isStrictParents();
        $doctrineTestData = $this->getDoctrineTestData();
        $graphTestData = $this->getGraphCompatibleData();
        $provider = array_merge($doctrineTestData, $graphTestData);
        $providerData = [];
        foreach ($provider as $datasetIndex => $dataset) {
            if ($isStrict && false === strpos($datasetIndex, '-strict')) {
                continue;
            }
            $providerData[$datasetIndex] = $dataset;
        }
        $providerData['__DATAPROVIDER__'] = true;
        return $providerData;
    }

    protected function executeDataProvider(string $method, $providerData)
    {
        if (null === $providerData) {
            return null;
        }
        if (is_array($providerData) && array_key_exists('__DATAPROVIDER__', $providerData)) {
            foreach ($providerData as $datasetIndex => $dataset) {
                if ('__DATAPROVIDER__' === $datasetIndex) {
                    continue;
                }
                $dataset['__DATASETINDEX__'] = $datasetIndex;
                $this->$method($dataset);
            }
            return null;
        }
        return $providerData;
    }

    /**********************
     * Test helpers
     *********************/

    protected function isSelectable($obj) : bool
    {
        return $obj instanceof Selectable;
    }

    private function isNameReadable($value)
    {
        if (!is_object($value)) {
            return false;
        }
        $constraints = $this->getNameConstraints();
        if (null === $constraints) {
            return false;
        }
        $propertyName = $constraints[$value];
        if (!is_string($propertyName)) {
            return false;
        }
        return $this->isPropertyValueReadable($value, $propertyName);
    }

    private function isParentReadable($value)
    {
        if (!is_object($value)) {
            return false;
        }
        $constraints = $this->getParentConstraints();
        if (null === $constraints) {
            return false;
        }
        $propertyName = $constraints[$value];
        if (!is_string($propertyName)) {
            return false;
        }
        return $this->isPropertyValueReadable($value, $propertyName);
    }

    private function hydrateElementKeys($elements)
    {
        $strictNames = $this->isStrictNames();
        $hasConstraints = $this->hasNameConstraints();
        if ($strictNames && !$hasConstraints) {
            throw new \Exception(sprintf('dataset "%s": Strict names true, but no name metadata defined'));
        }
        if ($strictNames || $hasConstraints) {
            $hydrated = [];
            $constraints = $this->getNameConstraints();
            foreach ($elements as $key => $value) {
                $isReadable = $this->isNameReadable($value);
                if ($strictNames && !$isReadable && is_object($value)) {
                    throw  new \Exception('strict: value not readable.');
                }
                if ($isReadable) {
                    $propertyName = $constraints[$value];
                    $newKey = $this->getPropertyValue($value, $propertyName);
                    $this->assertInternalType('string', $newKey);
                    $this->assertNotEmpty($newKey);
                    $key = $newKey;
                }
                $hydrated[$key] = $value;
            }
            $this->assertCount(count($elements), $hydrated);
            $elements = $hydrated;
        }
        return $elements;
    }

    private function hydrateElementParents(ArrayCollectionInterface $parent, $elements)
    {
        $isStrictParents = $this->isStrictParents();
        $hasConstraints = $this->hasParentConstraints();
        if ($isStrictParents && !$hasConstraints) {
            throw new \JBJ\Workflow\Exception\FixMeException(sprintf('dataset "%s". Strict parents true, but no parent metadata defined'));
        }
        if ($isStrictParents || $hasConstraints) {
            $constraints = $this->getNameConstraints();
            foreach ($elements as $key => $value) {
                $isReadable = $this->isNameReadable($value);
                //todo test for strict && !readable??
                if ($isReadable) {
                    $propertyName = $constraints[$value];
                    $newKey = $this->getPropertyValue($value, $propertyName);
                    $this->assertInternalType('string', $newKey);
                    $this->assertNotEmpty($newKey);
                    $key = $newKey;
                }
                $hydrated[$key] = $value;
            }
        }
    }

    /**********************
     * Shim tests
     *********************/

    /**
     * @group init
     */
    public function testAcceptableElement()
    {
        $element = $this->buildAcceptableElement('test.key');
        if ($this->hasNameConstraints()) {
            $isReadable = $this->isNameReadable($element);
            $this->assertTrue($isReadable);
        }
        if ($this->hasParentConstraints()) {
            $isReadable = $this->isParentReadable($element);
            $this->assertTrue($isReadable);
        }
        // used by selectable tests
        $isReadable = $this->isPropertyValueReadable($element, 'otherValue');
        $this->assertTrue($isReadable);
    }

    /**
     * @group init
     */
    public function testBuildCollection()
    {
        $collection = $this->buildCollection();
        $class = $this->getTestClass();
        $this->assertInstanceOf($class, $collection);
        return $collection;
    }

    /**
     * @depends testBuildCollection
     * @group init
     */
    public function testGraphCollectionInstrumentation($collection)
    {
        $isGraph = $this->isGraph();
        if (!$isGraph) {
            $this->markTestSkipped(sprintf('test requires graph collection, not "%s"', get_class($collection)));
        }
        foreach (['isStrictNames', 'isStrictParents', 'hasNameConstraints', 'hasParentConstraints'] as $method) {
            $this->assertSame($this->$method(), $collection->$method(), sprintf('Intstrumentation failure: "%s::%s"', get_class($collection), $method));
        }
        return $collection;
    }

    // used via depends for tests to get the data provider
    /**
     * @group init
     */
    public function testBuildDataProvider()
    {
        $providerData = $this->buildDataProvider();
        $expectedCount = $this->isStrictNames() || $this->isStrictParents() ? 3 : 6;
        $expectedCount++; // For data provider workflow
        $this->assertCount($expectedCount, $providerData);
        return $providerData;
    }

    /**
     * @depends testBuildDataProvider
     * @group init
     */
    public function testExecuteDataProviderNameHydration($providerData)
    {
        $elements = $this->executeDataProvider(__FUNCTION__, $providerData);
        if (null === $elements) {
            return;
        }
        if (array_key_exists('__DATASETINDEX__', $elements)) {
            $datasetIndex = $elements['__DATASETINDEX__'];
            unset($elements['__DATASETINDEX__']);
        }

        // actual test follows:
        $collection = $this->buildCollection($elements);
        $elements = $this->hydrateElementKeys($elements);
        $this->assertEquals($elements, $collection->toArray(), sprintf('dataset "%s": hydrated elements not same as collection "%s"', $datasetIndex, get_class($collection)));
    }

    /**
     * @depends testBuildDataProvider
     * @group init
     */
    public function testExecuteDataProviderParentHydration($providerData)
    {
        $elements = $this->executeDataProvider(__FUNCTION__, $providerData);
        if (null === $elements) {
            return;
        }
        if (array_key_exists('__DATASETINDEX__', $elements)) {
            $datasetIndex = $elements['__DATASETINDEX__'];
            unset($elements['__DATASETINDEX__']);
        }

        // actual test follows:
        $collection = $this->buildCollection($elements);
        $count = 0;
        foreach ($collection as $key => $element) {
            $isReadable = $this->isParentReadable($element);
            if (!$isReadable) {
                continue;
            }
            $constraints = $this->getParentConstraints();
            $propertyName = $constraints[$element];
            if (!is_string($propertyName)) {
                continue;
            }
            $parent = $this->getPropertyValue($element, $propertyName);
            $this->assertEquals($collection, $parent);
            $count++;
        }
        if (!$count) {
            $this->markTestSkipped(sprintf('dataset "%s": no valid elements in collection', $datasetIndex));
        }
    }

    /**********************
     * Unit Tests
     *********************/

    /** @depends testBuildDataProvider */
    public function testFirst($providerData)
    {
        $elements = $this->executeDataProvider(__FUNCTION__, $providerData);
        if (null === $elements) {
            return;
        }
        if (array_key_exists('__DATASETINDEX__', $elements)) {
            $datasetIndex = $elements['__DATASETINDEX__'];
            unset($elements['__DATASETINDEX__']);
        }

        // actual test follows:
        $collection = $this->buildCollection($elements);
        $elements = $this->hydrateElementKeys($providerData);
        $this->assertSame(reset($elements), $collection->first());
    }

    /** @depends testBuildDataProvider */
    public function testLast($providerData)
    {
        $elements = $this->executeDataProvider(__FUNCTION__, $providerData);
        if (null === $elements) {
            return;
        }
        if (array_key_exists('__DATASETINDEX__', $elements)) {
            $datasetIndex = $elements['__DATASETINDEX__'];
            unset($elements['__DATASETINDEX__']);
        }

        // actual test follows:
        $collection = $this->buildCollection($elements);
        $elements = $this->hydrateElementKeys($elements);
        $this->assertSame(end($elements), $collection->last());
    }

    /** @depends testBuildDataProvider */
    public function testNext($providerData)
    {
        $elements = $this->executeDataProvider(__FUNCTION__, $providerData);
        if (null === $elements) {
            return;
        }
        if (array_key_exists('__DATASETINDEX__', $elements)) {
            $datasetIndex = $elements['__DATASETINDEX__'];
            unset($elements['__DATASETINDEX__']);
        }

        // actual test follows:
        $collection = $this->buildCollection($elements);
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

    /** @depends testBuildDataProvider */
    public function testKey($providerData)
    {
        $elements = $this->executeDataProvider(__FUNCTION__, $providerData);
        if (null === $elements) {
            return;
        }
        if (array_key_exists('__DATASETINDEX__', $elements)) {
            $datasetIndex = $elements['__DATASETINDEX__'];
            unset($elements['__DATASETINDEX__']);
        }

        // actual test follows:
        $collection = $this->buildCollection($elements);
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

    /** @depends testBuildDataProvider */
    public function testCurrent($providerData)
    {
        $elements = $this->executeDataProvider(__FUNCTION__, $providerData);
        if (null === $elements) {
            return;
        }
        if (array_key_exists('__DATASETINDEX__', $elements)) {
            $datasetIndex = $elements['__DATASETINDEX__'];
            unset($elements['__DATASETINDEX__']);
        }

        // actual test follows:
        $collection = $this->buildCollection($elements);
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

    /** @depends testBuildDataProvider */
    public function testGetKeys($providerData)
    {
        $elements = $this->executeDataProvider(__FUNCTION__, $providerData);
        if (null === $elements) {
            return;
        }
        if (array_key_exists('__DATASETINDEX__', $elements)) {
            $datasetIndex = $elements['__DATASETINDEX__'];
            unset($elements['__DATASETINDEX__']);
        }

        // actual test follows:
        $collection = $this->buildCollection($elements);
        $elements = $this->hydrateElementKeys($elements);
        $this->assertSame(array_keys($elements), $collection->getKeys());
    }

    /** @depends testBuildDataProvider */
    public function testGetValues($providerData)
    {
        $elements = $this->executeDataProvider(__FUNCTION__, $providerData);
        if (null === $elements) {
            return;
        }
        if (array_key_exists('__DATASETINDEX__', $elements)) {
            $datasetIndex = $elements['__DATASETINDEX__'];
            unset($elements['__DATASETINDEX__']);
        }

        // actual test follows:
        $collection = $this->buildCollection($elements);
        // $elements = $this->hydrateElementKeys($elements);
        $this->assertSame(array_values($elements), $collection->getValues());
    }

    /** @depends testBuildDataProvider */
    public function testCount($providerData)
    {
        $elements = $this->executeDataProvider(__FUNCTION__, $providerData);
        if (null === $elements) {
            return;
        }
        if (array_key_exists('__DATASETINDEX__', $elements)) {
            $datasetIndex = $elements['__DATASETINDEX__'];
            unset($elements['__DATASETINDEX__']);
        }

        // actual test follows:
        $collection = $this->buildCollection($elements);
        // $elements = $this->hydrateElementKeys($elements);
        $this->assertSame(count($elements), $collection->count());
    }

    /** @depends testBuildDataProvider */
    public function testIterator($providerData)
    {
        $elements = $this->executeDataProvider(__FUNCTION__, $providerData);
        if (null === $elements) {
            return;
        }
        if (array_key_exists('__DATASETINDEX__', $elements)) {
            $datasetIndex = $elements['__DATASETINDEX__'];
            unset($elements['__DATASETINDEX__']);
        }

        // actual test follows:
        $collection = $this->buildCollection($elements);
        $elements = $this->hydrateElementKeys($elements);
        $iterations = 0;
        foreach ($collection->getIterator() as $key => $item) {
            $this->assertSame($elements[$key], $item, 'Item ' . $key . ' not match');
            ++$iterations;
        }

        $this->assertEquals(count($elements), $iterations, 'Number of iterations not match');
    }

    /** @depends testBuildDataProvider */
    public function testEmpty($providerData)
    {
        $elements = $this->executeDataProvider(__FUNCTION__, $providerData);
        if (null === $elements) {
            return;
        }
        if (array_key_exists('__DATASETINDEX__', $elements)) {
            $datasetIndex = $elements['__DATASETINDEX__'];
            unset($elements['__DATASETINDEX__']);
        }

        // actual test follows:
        $collection = $this->buildCollection();
        $this->assertTrue($collection->isEmpty(), 'Empty collection');
        foreach ($elements as $key => $value) {
            $collection[$key] = $value;
        }
        $this->assertFalse($collection->isEmpty(), 'Not empty collection for "' . $datasetIndex . '"."');
        $this->assertCount(count($elements), $collection, 'Wrong collection count for "' . $datasetIndex . '"."');
    }

    /** @depends testBuildDataProvider */
    public function testRemove($providerData)
    {
        $elements = $this->executeDataProvider(__FUNCTION__, $providerData);
        if (null === $elements) {
            return;
        }
        if (array_key_exists('__DATASETINDEX__', $elements)) {
            $datasetIndex = $elements['__DATASETINDEX__'];
            unset($elements['__DATASETINDEX__']);
        }

        // actual test follows:
        $missingElement = $this->buildAcceptableElement('test.id.zZ');
        $collection = $this->buildCollection($elements);
        $elements = $this->hydrateElementKeys($elements);
        $this->assertNull($collection->remove($missingElement->getName()));
        foreach ($elements as $key => $value) {
            $this->assertEquals($value, $collection->remove($key));
        }
        $this->assertTrue($collection->isEmpty(), 'Empty collection for "' . $datasetIndex . '"."');
    }

    /** @depends testBuildDataProvider */
    public function testRemoveElement($providerData)
    {
        $elements = $this->executeDataProvider(__FUNCTION__, $providerData);
        if (null === $elements) {
            return;
        }
        if (array_key_exists('__DATASETINDEX__', $elements)) {
            $datasetIndex = $elements['__DATASETINDEX__'];
            unset($elements['__DATASETINDEX__']);
        }

        // actual test follows:
        $missingElement = $this->buildAcceptableElement('test.id.zZ');
        $collection = $this->buildCollection($elements);
        $elements = $this->hydrateElementKeys($elements);
        $this->assertFalse($collection->removeElement($missingElement));
        foreach ($elements as $key => $value) {
            $this->assertTrue($collection->removeElement($value));
        }
        $this->assertTrue($collection->isEmpty(), 'Empty collection for "' . $datasetIndex . '"."');
    }

    /** @depends testBuildDataProvider */
    public function testContainsKey($providerData)
    {
        $elements = $this->executeDataProvider(__FUNCTION__, $providerData);
        if (null === $elements) {
            return;
        }
        if (array_key_exists('__DATASETINDEX__', $elements)) {
            $datasetIndex = $elements['__DATASETINDEX__'];
            unset($elements['__DATASETINDEX__']);
        }

        // actual test follows:
        $missingElement = $this->buildAcceptableElement('test.id.zZ');
        $collection = $this->buildCollection($elements);
        $elements = $this->hydrateElementKeys($elements);
        $this->assertFalse($collection->containsKey($missingElement->getName(), 'Missing key found for "' . $datasetIndex . '"."'));
        foreach ($elements as $key => $value) {
            $this->assertTrue($collection->containsKey($key), 'Expected key not found for "' . $datasetIndex . '"."');
        }
    }

    /** @depends testBuildDataProvider */
    public function testContains($providerData)
    {
        $elements = $this->executeDataProvider(__FUNCTION__, $providerData);
        if (null === $elements) {
            return;
        }
        if (array_key_exists('__DATASETINDEX__', $elements)) {
            $datasetIndex = $elements['__DATASETINDEX__'];
            unset($elements['__DATASETINDEX__']);
        }

        // actual test follows:
        $missingElement = $this->buildAcceptableElement('test.id.zZ');
        $collection = $this->buildCollection($elements);
        // $elements = $this->hydrateElementKeys($elements);
        $this->assertFalse($collection->contains($missingElement), 'Missing element found for "' . $datasetIndex . '"."');
        foreach ($elements as $key => $value) {
            $this->assertTrue($collection->contains($value), 'Expected element not found for "' . $datasetIndex . '"."');
        }
    }

    /** @depends testBuildDataProvider */
    public function testExists($providerData)
    {
        $elements = $this->executeDataProvider(__FUNCTION__, $providerData);
        if (null === $elements) {
            return;
        }
        if (array_key_exists('__DATASETINDEX__', $elements)) {
            $datasetIndex = $elements['__DATASETINDEX__'];
            unset($elements['__DATASETINDEX__']);
        }

        // actual test follows:
        $missingElement = $this->buildAcceptableElement('test.id.zZ');
        $collection = $this->buildCollection($elements);
        $elements = $this->hydrateElementKeys($elements);
        $this->assertFalse(isset($collection[$missingElement->getName()]), 'Missing key set for "' . $datasetIndex . '"."');
        foreach ($elements as $key => $value) {
            $this->assertTrue(isset($collection[$key]), 'Expected key not set for "' . $datasetIndex . '".');
        }
    }

    /** @depends testBuildDataProvider */
    public function testIndexOf($providerData)
    {
        $elements = $this->executeDataProvider(__FUNCTION__, $providerData);
        if (null === $elements) {
            return;
        }
        if (array_key_exists('__DATASETINDEX__', $elements)) {
            $datasetIndex = $elements['__DATASETINDEX__'];
            unset($elements['__DATASETINDEX__']);
        }

        // actual test follows:
        $missingElement = $this->buildAcceptableElement('test.id.zZ');
        $collection = $this->buildCollection($elements);
        $elements = $this->hydrateElementKeys($elements);
        $this->assertFalse($collection->indexOf($missingElement->getName()), 'Index of missing key found for "' . $datasetIndex . '"."');
        foreach ($elements as $key => $value) {
            $this->assertEquals($key, $collection->indexOf($value), 'Index of expected key not found for "' . $datasetIndex . '"."');
        }
    }

    /** @depends testBuildDataProvider */
    public function testGet($providerData)
    {
        $elements = $this->executeDataProvider(__FUNCTION__, $providerData);
        if (null === $elements) {
            return;
        }
        if (array_key_exists('__DATASETINDEX__', $elements)) {
            $datasetIndex = $elements['__DATASETINDEX__'];
            unset($elements['__DATASETINDEX__']);
        }

        // actual test follows:
        $missingElement = $this->buildAcceptableElement('test.id.zZ');
        $collection = $this->buildCollection($elements);
        $elements = $this->hydrateElementKeys($elements);
        $this->assertNull($collection[$missingElement->getName()], 'Missing element returned for "' . $datasetIndex . '"."');
        foreach ($elements as $key => $value) {
            $this->assertEquals($value, $collection[$key], 'Expected element not returned for "' . $datasetIndex . '"."');
        }
    }

    /** @depends testBuildDataProvider */
    public function testToString($providerData)
    {
        $elements = $this->executeDataProvider(__FUNCTION__, $providerData);
        if (null === $elements) {
            return;
        }
        if (array_key_exists('__DATASETINDEX__', $elements)) {
            $datasetIndex = $elements['__DATASETINDEX__'];
            unset($elements['__DATASETINDEX__']);
        }

        // actual test follows:
        $collection = $this->buildCollection($elements);
        $this->assertTrue(is_string((string) $collection));
    }

    /** @depends testBuildDataProvider */
    public function testIssetAndUnset($providerData)
    {
        $elements = $this->executeDataProvider(__FUNCTION__, $providerData);
        if (null === $elements) {
            return;
        }
        if (array_key_exists('__DATASETINDEX__', $elements)) {
            $datasetIndex = $elements['__DATASETINDEX__'];
            unset($elements['__DATASETINDEX__']);
        }

        // actual test follows:
        $collection = $this->buildCollection($elements);
        $elements = $this->hydrateElementKeys($elements);
        foreach ($elements as $key => $value) {
            $this->assertTrue(isset($collection[$key]));
            unset($collection[$key]);
            $this->assertFalse(isset($collection[$key]));
        }
    }
    /** @depends testBuildDataProvider */
    public function testMatchingWithSortingPreservesKeys($providerData)
    {
        $elements = $this->executeDataProvider(__FUNCTION__, $providerData);
        if (null === $elements) {
            return;
        }
        if (array_key_exists('__DATASETINDEX__', $elements)) {
            $datasetIndex = $elements['__DATASETINDEX__'];
            unset($elements['__DATASETINDEX__']);
        }
        if (false === strpos($datasetIndex, '-strict')) {
            return;
        }

        // actual test follows:
        $missingElement = $this->buildAcceptableElement('test.id.zZ');
        if (false === strpos($datasetIndex, '-strict')) {
            return;
        }
        $collection = $this->buildCollection($elements);
        $this->assertTrue($this->isSelectable($collection));
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

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

abstract class BaseNamedCollectionTest extends TestCase
{
    abstract protected function createCollection(string $name, array $elements = []);

    public function setUp()
    {
        $this->setTestClassname($this->createCollection('null'));
    }

    private $testClassname;
    protected function getTestClassname()
    {
        return $this->testClassname;
    }

    protected function setTestClassname($testClassname)
    {
        if (is_object($testClassname)) {
            $testClassname = get_class($testClassname);
        }
        if (!class_exists($testClassname)) {
            throw new \Exception(sprintf('Class "%s" not found', strval($testClassname)));
        }
        $this->testClassname = $testClassname;
    }

    protected function getTraitNames($classorname)
    {
        $classname = is_object($classorname) ? get_class($classorname) : $classorname;
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
        return $traitNames;
    }

    protected function hasTrait(string $class, string $trait)
    {
        $traitNames = $this->getTraitNames($class);
        return in_array($trait, $traitNames, true);
    }

    protected function isNodeCollection()
    {
        return $this->getTestClassname() instanceof NodeCollectionInterface;
    }

    protected function isNamedCollection()
    {
        return $this->getTestClassname() instanceof NamedCollectionInterface;
    }

    protected function isCollection()
    {
        return $this->getTestClassname() instanceof ArrayCollectionInterface;
    }

    /** @group init */
    public function testIsNodeCollection()
    {
        $isNode = $this->isNodeCollection();
        $hasTrait = $this->hasTrait($this->getTestClassname, NodeCollectionTrait::class);
        $this->assertEquals($isNode, $hasTrait);
    }

    /** @group init */
    public function testIsNamedCollection()
    {
        $isNamed = $this->isNamedCollection();
        $hasTrait = $this->hasTrait($this->getTestClassname, NamedCollectionTrait::class);
        $this->assertEquals($isNamed, $hasTrait);
    }

    /** @group init */
    public function testIsCollection()
    {
        $isCollection = $this->isCollection();
        $hasTrait = $this->hasTrait($this->getTestClassname, CollectionTrait::class);
        $this->assertEquals($isCollection, $hasTrait);
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

    protected function createNodeCollectionElement()
    {
        $element = new class() implements NodeCollectionInterface {
            use NodeCollectionTrait;
        };
        return $element;
    }

    /** @group init */
    public function testCreateNodeCollectionElement()
    {
        $element = $this->createNodeCollectioneElement('node-collection.element');
        $this->assertInstanceOf(NodeCollectionInterface::class, $element);
        $this->assertInstanceOf(NodeInterface::class, $element);
        $this->assertTrue(method_exists($element, 'getOtherValue'));
        $this->assertTrue(method_exists($element, 'setOtherValue'));
    }

    protected function createNameCollectionElement()
    {
        $element = new class() implements NodeCollectionInterface {
            use NamedCollectionTrait;
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

    /** @group init */
    public function testCreateNamedCollectionElement()
    {
        $element = $this->createNamedCollectionElement('named-collection.element');
        $this->assertInstanceOf(ArrayCollectionInterface::class, $element);
        $this->assertInstanceOf(NodeInterface::class, $element);
        $this->assertTrue(method_exists($element, 'getOtherValue'));
        $this->assertTrue(method_exists($element, 'setOtherValue'));
    }

    protected function createCollectionElement()
    {
        $element = new class() implements ArrayCollectionInterface {
            use CollectionTrait;
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

    /** @group init */
    public function testCreateCollectionElement()
    {
        $element = $this->createCollectionElement('collection.element');
        $this->assertInstanceOf(ArrayCollectionInterface::class, $element);
        $this->assertTrue(method_exists($element, 'getOtherValue'));
        $this->assertTrue(method_exists($element, 'setOtherValue'));
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
        };
        return $element;
    }

    /** @group init */
    public function testCreateAcceptableElement()
    {
        $element = $this->createAcceptableElement('acceptable.element');
        $this->assertTrue(method_exists($element, 'getName'));
        $this->assertTrue(method_exists($element, 'getParent'));
        $this->assertTrue(method_exists($element, 'setParent'));
        $this->assertTrue(method_exists($element, 'getOtherValue'));
        $this->assertTrue(method_exists($element, 'setOtherValue'));
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


    /** * @group init * @depends testGraphDataProvider */
    public function testHydrateElementKeys($providerData)
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

    protected function buildSimpleDataProvider()
    {
        $providers = $this->getDoctrineTestData();
        $providers['__DATAPROVIDER__'] = 'simple';
        return $providers;
    }

    protected function buildNodeDataProvider()
    {
        $providers = $this->getNodeCompatibleData();
        $providers['__DATAPROVIDER__'] = 'graph';
        return $providers;
    }

    protected function buildCompleteDataProvider()
    {
        $doctrineTestData = $this->getDoctrineTestData();
        $nodeTestData = $this->getNodeCompatibleData();
        $providers = array_merge($doctrineTestData, $nodeTestData);
        $providers['__DATAPROVIDER__'] = 'complete';
        return $providers;
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

    /** * @group init * This is the simple data provider */
    public function testSimpleDataProvider()
    {
        $providerData = $this->buildSimpleDataProvider();
        $this->assertEquals('simple', $providerData['__DATAPROVIDER__']);
        return $providerData;
    }

    /** * @group init * This is the graph data provider */
    public function testGraphDataProvider()
    {
        $providerData = $this->buildGraphDataProvider();
        $this->assertEquals('graph', $providerData['__DATAPROVIDER__']);
        return $providerData;
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

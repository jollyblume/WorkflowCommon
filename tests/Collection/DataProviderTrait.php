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
    private $testClassname;
    protected function getTestClassname()
    {
        $classname = $this->testClassname;
        return $classname;
    }

    protected function setTestClassname($class)
    {
        $classname = is_object($class) ? get_class($class) : strval($class);
        if (!class_exists($classname)) {
            throw new \Exception(sprintf('Class "%s" not found', $classname));
        }
        $this->testClassname = $classname;
        $relevantNamesFixture = [
            CollectionTrait::class,
            LeafCollectionTrait::class,
            NodeCollectionTrait::class,
        ];
        $traitNames = $this->getTraitNames($classname);
        $relevantNames = array_intersect($relevantNamesFixture, $traitNames);
        if (count($relevantNames) === 0) {
            throw new \Exception(sprintf('No relative names found in "%s"', join(',', $traitNames)));
        }
        $this->relevantTraitName = reset($relevantNames);
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
        return $relevantName;
    }

    protected function createTestCollection(string $name, array $elements = [])
    {
        $testClassname = $this->getTestClassname();
        if ($this->isCollection()) {
            return new $testClassname($elements);
        }
        return new $testClassname($name, $elements);
    }

    protected function createCollection(array $elements = [])
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

    protected function createLeafCollection(string $name, array $elements = [])
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

    protected function createNodeCollection(string $name, array $elements = [])
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
            NodeCollectionTrait::class => 'createLeafCollection',
            LeafCollectionTrait::class => 'createCollection',
            CollectionTrait::class => 'createCollection',
        ];
        $relevantName = $this->getRelevantTraitName();
        $method = $allowedNames[$relevantName];
        if ($this->isNodeCollection()) {
            return $this->$method($name, $elements);
        }
        return $this->$method($elements);
    }

    protected function isNodeCollection()
    {
        $isNodeCollection = $this->getRelevantTraitName() === NodeCollectionTrait::class;
        return $isNodeCollection;
    }

    protected function isCollection()
    {
        $isNodeCollection = $this->getRelevantTraitName() === CollectionTrait::class;
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

    protected function getDataProvider()
    {
        $data = $this->getDataForTestCase();
        $data['__DATAPROVIDER__'] = true;
        return $data;
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
}

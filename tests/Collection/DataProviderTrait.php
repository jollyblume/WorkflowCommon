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
}

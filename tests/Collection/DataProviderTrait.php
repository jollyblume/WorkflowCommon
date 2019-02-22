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

    protected function createCollection(array $elements = [])
    {
        $collection = new class($elements) implements ArrayCollectionInterface {
            use CollectionTrait;
            public function __construct(array $elements = [])
            {
                $this->saveElements($elements);
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
        if (NodeCollectionTrait::class === $relevantName) {
            return $this->$method($name, $elements);
        }
        return $this->$method($elements);
    }
}

<?php

namespace JBJ\Workflow\Tests\Collection;

use PHPUnit\Framework\TestCase;

trait DataProviderTrait
{
    private $testClassname;
    private $relevantTraitNames;
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
        $this->relevantTraitNames = $this->getRelevantTraitNames($classname);
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
        return $traitNames;
    }

    protected function getRelevantTraitNames($class)
    {
        $relevantNames = [
            'JBJ\Workflow\Collection\CollectionTrait',
            'JBJ\Workflow\Collection\NamedCollectionTrait',
            'JBJ\Workflow\Collection\NodeCollectionTrait',
        ];
        $traitNames = $this->getTraitNames($class);
        return array_intersect($relevantNames, $traitNames);
    }
}

<?php

namespace JBJ\Workflow\Tests\Collection;

use JBJ\Workflow\Collection\ArrayCollectionInterface;
use JBJ\Workflow\Collection\GraphCollection;

class GraphCollectionCompatibilityTest extends BaseCollectionTraitTest
{
    protected function getTestClass() : string
    {
        return GraphCollection::class;
    }

    protected function getRules() : array
    {
        $rules = [
            'name' => [
                'name',
                'isDisabled' => false,
                'isValid' => true,
            ],
            'parent' => [
                'parent',
                'isDisabled' => false,
                'isValid' => true,
            ],
            'unused-rule' => [],
        ];
        return $rules;
    }

    protected function createCollection(string $name, array $elements = []) : ArrayCollectionInterface
    {
        $rules = $this->getRules();
        $propertyAccessor = $this->getPropertyAccessor();
        $testClass = $this->getTestClass();
        $collection = new $testClass($name, $elements, $rules, $propertyAccessor);
        return $collection;
    }

    public function testGetUnusedRules()
    {
        $collection = $this->createCollection('test');
        $expectedUnusedRules = [
            'unused-rule' => [],
        ];
        $this->assertEquals($expectedUnusedRules, $collection->getUnusedRules()->toArray());
    }

    public function testGetUnusedRulesWhenNull()
    {
        $rules = [
            'name' => [
                'name',
                'isDisabled' => false,
                'isValid' => true,
            ],
            'parent' => [
                'parent',
                'isDisabled' => false,
                'isValid' => true,
            ],
        ];
        $propertyAccessor = $this->getPropertyAccessor();
        $testClass = $this->getTestClass();
        $collection = new $testClass('test', [], $rules, $propertyAccessor);
        $this->assertEquals([], $collection->getUnusedRules()->toArray());
    }

    /** @expectedException \JBJ\Workflow\Exception\FixMeException */
    public function testAssertRequiredRules()
    {
        $testClass = $this->getTestClass();
        $collection = new $testClass('test');
        $this->assertEquals([], $collection->getUnusedRules()->toArray());
    }
}

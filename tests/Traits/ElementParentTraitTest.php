<?php

namespace JBJ\Workflow\Tests\Traits;

use Closure;
use JBJ\Workflow\Traits\ElementParentTrait;
use PHPUnit\Framework\TestCase;

class ElementParentTraitTest extends TestCase
{
    public function testIfEmpty()
    {
        $trait = new class() {
            use ElementParentTrait;
        };
        $this->assertNull($trait->getParent());
        $parent = new class() {
        };
        $trait->setParent($parent);
        $this->assertEquals($parent, $trait->getParent());
    }

    protected function getResursiveTrait(string $name = null, $parent = null, $testValue = null)
    {
        $trait = new class($name, $parent, $testValue) {
            use ElementParentTrait;
            private $name;
            private $testValue;
            public function __construct($name, $parent, $testValue)
            {
                $this->setName($name);
                $this->setParent($parent);
                $this->setTestValue($testValue);
            }
            public function getName()
            {
                return $this->name;
            }
            public function setName(string $name)
            {
                $this->name = $name;
            }
            public function getTestValue()
            {
                return $this->testValue;
            }
            public function setTestValue($testValue)
            {
                $this->testValue = $testValue;
            }
        };
        return $trait;
    }

    protected function getRecursiveTraitNames()
    {
        return ['first-parent', 'second-parent', 'third-parent'];
    }

    protected function getRecursiveGraph()
    {
        $traits = [];
        $traitNames = $this->getRecursiveTraitNames();
        $parent = null;
        foreach ($traitNames as $traitName) {
            $trait = $this->getResursiveTrait($traitName, $parent);
            $parent = $trait;
            $traits[] = $trait;
        }
        return $traits;
    }

    public function testRecursiveGetValueNullIfValueNotSet()
    {
        $traits = $this->getRecursiveGraph();
        $finalTrait = end($traits);
        $value = $finalTrait->getValueForMethod('getTestValue');
        $this->assertNull($value);
    }

    public function testRecursiveGetIfValueSetInCurrentTrait()
    {
        $traits = $this->getRecursiveGraph();
        foreach ($traits  as $trait) {
            $value = $trait->getValueForMethod('getName');
            $this->assertEquals($trait->getName(), $value);
        }
    }

    public function testRecursiveGetIfValueNotSetInCurrentTrait()
    {
        $traits = $this->getRecursiveGraph();
        $traits[0]->setTestValue('test.value');
        $finalTrait = end($traits);
        $value = $finalTrait->getValueForMethod('getTestValue');
        $this->assertEquals('test.value', $value);
    }
}

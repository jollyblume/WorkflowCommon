<?php

namespace JBJ\Workflow\Tests\Traits;

use Closure;
use Doctrine\Common\Collections\ArrayCollection;
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

    public function testRecursiveGetParentForValue()
    {
        $traits = $this->getRecursiveGraph();
        $finalTrait = end($traits);
        $traitNames = $this->getRecursiveTraitNames();
        $traitMap = array_combine($traitNames, $traits);
        foreach ($traitNames as $traitName) {
            $parent = $finalTrait->getParentForValue('getName', $traitName);
            $this->assertEquals($traitMap[$traitName], $parent);
        }
    }

    public function testRecursiveGetRootParent()
    {
        $traits = $this->getRecursiveGraph();
        $rootTrait = $traits[0];
        $traitNames = $this->getRecursiveTraitNames();
        $traitMap = array_combine($traitNames, $traits);
        foreach ($traitNames as $traitName) {
            $trait = $traitMap[$traitName];
            $parent = $trait->getRootParent();
            $this->assertEquals($rootTrait, $parent);
        }
    }

    protected function getAnchoredRecursiveGraph()
    {
        $traits = $this->getRecursiveGraph();
        $traits[0]->setParent(new ArrayCollection());
        return $traits;
    }

    public function testAnchoredRecursiveGetValueNullIfValueNotSet()
    {
        $traits = $this->getAnchoredRecursiveGraph();
        $finalTrait = end($traits);
        $value = $finalTrait->getValueForMethod('getTestValue');
        $this->assertNull($value);
    }

    public function testAnchoredRecursiveGetIfValueSetInCurrentTrait()
    {
        $traits = $this->getAnchoredRecursiveGraph();
        foreach ($traits  as $trait) {
            $value = $trait->getValueForMethod('getName');
            $this->assertEquals($trait->getName(), $value);
        }
    }

    public function testAnchoredRecursiveGetIfValueNotSetInCurrentTrait()
    {
        $traits = $this->getAnchoredRecursiveGraph();
        $traits[0]->setTestValue('test.value');
        $finalTrait = end($traits);
        $value = $finalTrait->getValueForMethod('getTestValue');
        $this->assertEquals('test.value', $value);
    }

    public function testAnchoredRecursiveGetParentForValue()
    {
        $traits = $this->getAnchoredRecursiveGraph();
        $finalTrait = end($traits);
        $traitNames = $this->getRecursiveTraitNames();
        $traitMap = array_combine($traitNames, $traits);
        foreach ($traitNames as $traitName) {
            $parent = $finalTrait->getParentForValue('getName', $traitName);
            $this->assertEquals($traitMap[$traitName], $parent);
        }
    }

    public function testAnchoredRecursiveGetRootParent()
    {
        $traits = $this->getAnchoredRecursiveGraph();
        $rootTrait = $traits[0];
        $traitNames = $this->getRecursiveTraitNames();
        $traitMap = array_combine($traitNames, $traits);
        foreach ($traitNames as $traitName) {
            $trait = $traitMap[$traitName];
            $parent = $trait->getRootParent();
            $this->assertEquals($rootTrait, $parent);
        }
    }
}

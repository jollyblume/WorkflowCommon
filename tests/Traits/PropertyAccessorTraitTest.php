<?php

namespace JBJ\Workflow\Tests\Traits;

use Symfony\Component\PropertyAccess\PropertyAccessorInterface;
// use Symfony\Component\PropertyAccess\PropertyAccess;
use JBJ\Workflow\Traits\PropertyAccessorTrait;
use PHPUnit\Framework\TestCase;

class PropertyAccessorTraitTest extends TestCase
{
    protected function getTrait()
    {
        $trait = new class() {
            use PropertyAccessorTrait {
                setPropertyAccessor as public;
                createPropertyAccessor as public;
            }
        };
        return $trait;
    }

    public function testEarlyGetReturnsNull()
    {
        $trait = $this->getTrait();
        $this->assertNull($trait->getPropertyAccessor());
    }

    public function testGetReturnsSetValue()
    {
        $trait = $this->getTrait();
        $propertyAccessor = $trait->createPropertyAccessor();
        $trait->setPropertyAccessor($propertyAccessor);
        $this->assertEquals($propertyAccessor, $trait->getPropertyAccessor());
    }

    public function testCreatePropertyAccessor()
    {
        $trait = $this->getTrait();
        $propertyAccessor = $trait->createPropertyAccessor();
        $this->assertInstanceOf(PropertyAccessorInterface::class, $propertyAccessor);
    }

    // public function testFindPropertyAccessorCanBeSetNull()
    // {
    //     $trait = $this->getTrait();
    //     $trait->setPropertyAccessor(null);
    //     $this->assertNull($trait->getPropertyAccessor());
    // }
    //
    // protected function getResursiveTrait(string $name = null, $parent = null, $testValue = null)
    // {
    //     $trait = new class($name, $parent, $testValue) {
    //         use ElementParentTrait;
    //         use PropertyAccessorTrait{
    //             setPropertyAccessor as public;
    //             setPersistPropertyAccessorHere as public;
    //             persistPropertyAccessorHere as public;
    //             setPersistPropertyAccessorHere as public;
    //         }
    //         private $name;
    //         private $testValue;
    //         public function __construct($name, $parent, $testValue)
    //         {
    //             $this->setName($name);
    //             $this->setParent($parent);
    //             $this->setTestValue($testValue);
    //         }
    //         public function getName()
    //         {
    //             return $this->name;
    //         }
    //         public function setName(string $name)
    //         {
    //             $this->name = $name;
    //         }
    //         public function getTestValue()
    //         {
    //             return $this->testValue;
    //         }
    //         public function setTestValue($testValue)
    //         {
    //             $this->testValue = $testValue;
    //         }
    //     };
    //     return $trait;
    // }
    //
    // protected function getRecursiveTraitNames()
    // {
    //     return ['first-parent', 'second-parent', 'third-parent'];
    // }
    //
    // protected function getRecursiveGraph()
    // {
    //     $traits = [];
    //     $traitNames = $this->getRecursiveTraitNames();
    //     $parent = null;
    //     foreach ($traitNames as $traitName) {
    //         $trait = $this->getResursiveTrait($traitName, $parent);
    //         $parent = $trait;
    //         $traits[] = $trait;
    //     }
    //     return $traits;
    // }
    //
    // /** @SuppressWarnings(PHPMD.StaticAccess) */
    // public function testFindPropertyAccessor()
    // {
    //     $traits = $this->getRecursiveGraph();
    //     $propertyAccessor = PropertyAccess::createPropertyAccessor();
    //     $traits[0]->setPropertyAccessor($propertyAccessor);
    //     foreach ($traits as $trait) {
    //         $value = $trait->findPropertyAccessor();
    //         $this->assertEquals($propertyAccessor, $value);
    //     }
    // }
    //
    // public function testFindPropertyAccessorDefault()
    // {
    //     $traits = $this->getRecursiveGraph();
    //     foreach ($traits as $trait) {
    //         $value = $trait->findPropertyAccessor();
    //         $this->assertInstanceOf(PropertyAccessorInterface::class, $value);
    //     }
    // }
    //
    // public function testHereDefaults()
    // {
    //     $trait = $this->getResursiveTrait('test.trait');
    //     $this->assertFalse($trait->persistPropertyAccessorHere());
    //     $this->assertNull($trait->getPropertyAccessor());
    // }
    //
    // public function testHereDefaultsWhenTrue()
    // {
    //     $trait = $this->getResursiveTrait('test.trait');
    //     $trait->setPersistPropertyAccessorHere(true);
    //     $this->assertInstanceOf(PropertyAccessorInterface::class, $trait->getPropertyAccessor());
    //     $this->assertTrue($trait->persistPropertyAccessorHere());
    // }
    //
    // public function testHereGraph()
    // {
    //     $traits = $this->getRecursiveGraph();
    //     $traits[0]->setPersistPropertyAccessorHere(true);
    //     $this->assertTrue($traits[0]->persistPropertyAccessorHere());
    //     $trait = end($traits);
    //     $propertyAccessor = $trait->findPropertyAccessor();
    //     $traits[0]->setPersistPropertyAccessorHere(false);
    //     $this->assertEquals($propertyAccessor, $traits[0]->getPropertyAccessor());
    // }
}

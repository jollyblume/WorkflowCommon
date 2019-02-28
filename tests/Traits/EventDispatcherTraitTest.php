<?php

namespace JBJ\Workflow\Tests\Traits;

use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use JBJ\Workflow\Traits\EventDispatcherTrait;
use PHPUnit\Framework\TestCase;

class EventDispatcherTraitTest extends TestCase
{
    protected function getTrait()
    {
        $trait = new class() {
            use EventDispatcherTrait {setDispatcher as public;}
        };
        return $trait;
    }

    public function testEarlyGetReturnsNull()
    {
        $trait = $this->getTrait();
        $this->assertNull($trait->getDispatcher());
    }

    public function testGetReturnsSetValue()
    {
        $trait = $this->getTrait();
        $dispatcher = $this->getMockBuilder(EventDispatcherInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $trait->setDispatcher($dispatcher);
        $this->assertEquals($dispatcher, $trait->getDispatcher());
    }

    // protected function getResursiveTrait(string $name = null, $parent = null, $testValue = null)
    // {
    //     $trait = new class($name, $parent, $testValue) {
    //         use EventDispatcherTrait{
    //             setDispatcher as public;
    //             getDispatcher as protected;
    //         }
    //         use ElementParentTrait;
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
    // public function testFindDispatcher()
    // {
    //     $traits = $this->getRecursiveGraph();
    //     $dispatcher = $this->getMockBuilder(EventDispatcherInterface::class)
    //         ->disableOriginalConstructor()
    //         ->getMock();
    //     $traits[0]->setDispatcher($dispatcher);
    //     foreach ($traits as $trait) {
    //         $value = $trait->findDispatcher();
    //         $this->assertEquals($dispatcher, $value);
    //     }
    // }
}

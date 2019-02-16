<?php

namespace JBJ\Workflow\Tests\Traits;

use JBJ\Workflow\Collection\ArrayCollectionInterface;
use JBJ\Workflow\Traits\ExpectedInterfaceTrait;
use PHPUnit\Framework\TestCase;

class ExpectedInterfaceTraitTest extends TestCase
{
    protected function getTrait()
    {
        $trait = new class() {
            use ExpectedInterfaceTrait {
                getExpectedInterfaces as public;
                setExpectedInterfaces as public;
                hasExpectedInterface as public;
                assertExpectedInterface as public;
            }
        };
        return $trait;
    }

    protected function getExpectedInterfacesFixture()
    {
        $expectedInterfaces = [
            \Traversable::class,
        ];
        return $expectedInterfaces;
    }

    public function testHas()
    {
        $trait = $this->getTrait();
        $trait->setExpectedInterfaces($this->getExpectedInterfacesFixture());
        $this->assertTrue($trait->hasExpectedInterface(\Traversable::class));
    }

    public function testHasNot()
    {
        $trait = $this->getTrait();
        $trait->setExpectedInterfaces($this->getExpectedInterfacesFixture());
        $this->assertFalse($trait->hasExpectedInterface(ArrayCollectionInterface::class));
    }

    public function testHasNotWithObject()
    {
        $trait = $this->getTrait();
        $trait->setExpectedInterfaces($this->getExpectedInterfacesFixture());
        $this->assertFalse($trait->hasExpectedInterface($this));
    }

    public function testGetEmptyByDefault()
    {
        $trait = $this->getTrait();
        $this->assertEquals([], $trait->getExpectedInterfaces());
    }

    public function testSet()
    {
        $expectedInterfaces = $this->getExpectedInterfacesFixture();
        $trait = $this->getTrait();
        $trait->setExpectedInterfaces($expectedInterfaces);
        $this->assertEquals($expectedInterfaces, $trait->getExpectedInterfaces());
    }
}

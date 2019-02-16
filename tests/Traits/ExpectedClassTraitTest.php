<?php

namespace JBJ\Workflow\Tests\Traits;

use JBJ\Workflow\Collection\ArrayCollectionInterface;
use JBJ\Workflow\Collection\CollectionTrait;
use JBJ\Workflow\Traits\ExpectedClassTrait;
use PHPUnit\Framework\TestCase;

class ExpectedClassTraitTest extends TestCase
{
    protected function getTrait()
    {
        $trait = new class() {
            use ExpectedClassTrait {
                getExpectedClasses as public;
                setExpectedClasses as public;
                hasExpectedClasses as public;
                assertExpectedClasses as public;
            }
        };
        return $trait;
    }

    protected function getExpectedClassFixture()
    {
        $expectedClass = [
            \Traversable::class,
            \ArrayAccess::class,
            ArrayCollectionInterface::class,
        ];
        return $expectedClass;
    }

    protected function getTestClass()
    {
        $testClass = new class() implements ArrayCollectionInterface {
            use CollectionTrait;

            public function __construct(array $elements = [])
            {
                $this->saveElements($elements);
            }
        };
        return $testClass;
    }

    public function testGetEmptyByDefault()
    {
        $trait = $this->getTrait();
        $this->assertEquals([], $trait->getExpectedClasses());
    }

    public function testSet()
    {
        $expectedClasses = $this->getExpectedClassFixture();
        $trait = $this->getTrait();
        $trait->setExpectedClasses($expectedClasses);
        $this->assertEquals($expectedClasses, $trait->getExpectedClasses());
    }

    public function testHas()
    {
        $expectedClasses = $this->getExpectedClassFixture();
        $trait = $this->getTrait();
        $trait->setExpectedClasses($expectedClasses);
        $testClass = $this->getTestClass();
        $this->assertTrue($trait->hasExpectedClasses($testClass));
    }

    public function testHasNot()
    {
        $expectedClasses = $this->getExpectedClassFixture();
        $trait = $this->getTrait();
        $trait->setExpectedClasses($expectedClasses);
        $this->assertFalse($trait->hasExpectedClasses($this));
    }

    public function testAssertOk()
    {
        $expectedClasses = $this->getExpectedClassFixture();
        $trait = $this->getTrait();
        $trait->setExpectedClasses($expectedClasses);
        $testClass = $this->getTestClass();
        $this->assertNull($trait->assertExpectedClasses($testClass));
    }

    /** @expectedException \JBJ\Workflow\Exception\FixMeException */
    public function testAssertThrows()
    {
        $expectedClasses = $this->getExpectedClassFixture();
        $trait = $this->getTrait();
        $trait->setExpectedClasses($expectedClasses);
        $this->assertNull($trait->assertExpectedClasses($this));
    }
}

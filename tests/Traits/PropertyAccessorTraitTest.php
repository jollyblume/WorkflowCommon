<?php

namespace JBJ\Workflow\Tests\Traits;

use Symfony\Component\PropertyAccess\PropertyAccess;
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

    /** @expectedException \JBJ\Workflow\Exception\FixMeException */
    public function testEarlyGetThrowsException()
    {
        $trait = $this->getTrait();
        $trait->getPropertyAccessor();
    }

    public function testGetReturnsSetValue()
    {
        $trait = $this->getTrait();
        $propertyAccessor = $trait->createPropertyAccessor();
        $trait->setPropertyAccessor($propertyAccessor);
        $this->assertEquals($propertyAccessor, $trait->getPropertyAccessor());
    }
}

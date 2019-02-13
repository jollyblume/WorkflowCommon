<?php

namespace JBJ\Workflow\Tests\Traits;

use JBJ\Workflow\Traits\ElementParentTrait;
use PHPUnit\Framework\TestCase;

class ElementParentTraitTest extends TestCase
{
    public function testIfEmpty()
    {
        $trait = new class()
        {
            use ElementParentTrait;
        };

        $this->assertNull($trait->getParent());
        $parent = new class() {};
        $trait->setParent($parent);
        $this->assertEquals($parent, $trait->getParent());
    }
}

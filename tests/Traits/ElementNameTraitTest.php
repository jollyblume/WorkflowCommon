<?php

namespace JBJ\Workflow\Tests\Traits;

use JBJ\Workflow\Traits\ElementNameTrait;
use PHPUnit\Framework\TestCase;

class ElementNameTraitTest extends TestCase
{
    public function testSetName()
    {
        $uuid = 'cd6ccde3-d11d-432b-8ffa-3596f214f7b1';
        $trait = new class($uuid)
        {
            use ElementNameTrait;

            public function __construct(string $name)
            {
                $this->setName($name);
            }
        };

        $this->assertEquals($uuid, $trait->getName());
    }
}

<?php

namespace JBJ\Workflow\Tests\Traits;

use JBJ\Workflow\Traits\NameTrait;
use PHPUnit\Framework\TestCase;

class NameTraitTest extends TestCase
{
    public function testSetName()
    {
        $uuid = 'cd6ccde3-d11d-432b-8ffa-3596f214f7b1';
        $trait = new class($uuid) {
            use NameTrait;

            public function __construct(string $name)
            {
                $this->setName($name);
            }
        };
        $this->assertEquals($uuid, $trait->getName());
    }

    public function testToString()
    {
        $trait = new class('test.name') {
            use NameTrait;

            public function __construct(string $name)
            {
                $this->setName($name);
            }
        };
        $this->assertEquals('test.name', strval($trait));
    }
}

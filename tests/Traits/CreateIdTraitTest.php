<?php

namespace JBJ\Workflow\Tests\Traits;

use JBJ\Workflow\Traits\CreateIdTrait;
use JBJ\Workflow\Validator\UuidValidator;
use PHPUnit\Framework\TestCase;

class CreateIdTraitTest extends TestCase
{
    public function testCreateId()
    {
        $trait = new class()
        {
            use CreateIdTrait {
                CreateId as public;
            }

            public function validate($id)
            {
                $validator = new UuidValidator();
                return $validator->validate($id);
            }
        };

        $this->assertFalse($trait->validate('a.name'));
        $uuid = 'cd6ccde3-d11d-432b-8ffa-3596f214f7b1';
        $this->assertTrue($trait->validate($uuid));
        $id = $trait->CreateId();
        $this->assertTrue($trait->validate($id));
        $id = $trait->CreateId('a.name');
        $this->assertTrue($trait->validate($id));
        $this->assertEquals($id, $trait->CreateId('a.name'));
        $id = $trait->CreateId($uuid);
        $this->assertEquals($uuid, $id);
    }
}

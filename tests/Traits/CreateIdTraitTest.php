<?php

namespace JBJ\Workflow\Tests\Traits;

use JBJ\Workflow\Traits\CreateIdTrait;
use Ramsey\Uuid\Validator\Validator as UuidValidator;
use PHPUnit\Framework\TestCase;

class CreateIdTraitTest extends TestCase
{
    public function testCreateId()
    {
        $trait = new class() {
            use CreateIdTrait {
                CreateId as public;
            }

            public function validate($uuid)
            {
                $validator = new UuidValidator();
                return $validator->validate($uuid);
            }
        };

        $this->assertFalse($trait->validate('a.name'));
        $uuid = 'cd6ccde3-d11d-432b-8ffa-3596f214f7b1';
        $this->assertTrue($trait->validate($uuid));
        $newId = $trait->CreateId();
        $this->assertTrue($trait->validate($newId));
        $newId = $trait->CreateId('a.name');
        $this->assertTrue($trait->validate($newId));
        $this->assertEquals($newId, $trait->CreateId('a.name'));
        $newId = $trait->CreateId($uuid);
        $this->assertEquals($uuid, $newId);
    }
}

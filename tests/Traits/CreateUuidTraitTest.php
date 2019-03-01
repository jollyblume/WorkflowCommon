<?php

namespace JBJ\Workflow\Tests\Traits;

use JBJ\Workflow\Traits\CreateUuidTrait;
use Ramsey\Uuid\Validator\Validator as UuidValidator;
use PHPUnit\Framework\TestCase;

class CreateUuidTraitTest extends TestCase
{
    public function testCreateUuid()
    {
        $trait = new class() {
            use CreateUuidTrait {
                createUuid as public;
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
        $newId = $trait->CreateUuid();
        $this->assertTrue($trait->validate($newId));
        $newId = $trait->CreateUuid('a.name');
        $this->assertTrue($trait->validate($newId));
        $this->assertEquals($newId, $trait->CreateUuid('a.name'));
        $newId = $trait->CreateUuid($uuid);
        $this->assertEquals($uuid, $newId);
    }
}

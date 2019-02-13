<?php

namespace JBJ\Workflow\Traits;

use Ramsey\Uuid\Uuid;
use JBJ\Workflow\Validator\UuidValidator;

trait CreateIdTrait
{
    protected function createId(string $name = '')
    {
        if (empty($name)) {
            return strval(Uuid::uuid4());
        }
        $validator = new UuidValidator();
        if ($validator->validate($name)) {
            return $name;
        }
        return strval(Uuid::uuid3(Uuid::NAMESPACE_DNS, $name));
    }
}

<?php

namespace JBJ\Workflow\Traits;

use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\Validator\UuidValidator;

trait CreateIdTrait
{
    /** @SuppressWarnings(PHPMD.StaticAccess) */
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

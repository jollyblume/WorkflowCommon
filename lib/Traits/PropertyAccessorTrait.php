<?php

namespace JBJ\Workflow\Traits;

use Symfony\Component\PropertyAccess\PropertyAccessorInterface;
use Symfony\Component\PropertyAccess\PropertyAccess;

trait PropertyAccessorTrait
{
    private $propertyAccessor;

    public function getPropertyAccessor()
    {
        return $this->propertyAccessor;
    }

    public function setPropertyAccessor(PropertyAccessorInterface $propertyAccessor)
    {
        $this->propertyAccessor = $propertyAccessor;
    }
}

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

    protected function setPropertyAccessor(PropertyAccessorInterface $propertyAccessor)
    {
        $this->propertyAccessor = $propertyAccessor;
    }

    /** @SuppressWarnings(PHPMD.StaticAccess) */
    protected function createPropertyAccessor()
    {
        return PropertyAccess::createPropertyAccessor();
    }
}

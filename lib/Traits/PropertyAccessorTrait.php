<?php

namespace JBJ\Workflow\Traits;

use Symfony\Component\PropertyAccess\PropertyAccessorInterface;
use Symfony\Component\PropertyAccess\PropertyAccess;

trait PropertyAccessorTrait
{
    private $propertyAccessor;
    private $persistPropertyAccessorHere = false;

    public function getPropertyAccessor()
    {
        return $this->propertyAccessor;
    }

    protected function setPropertyAccessor(PropertyAccessorInterface $propertyAccessor)
    {
        $this->propertyAccessor = $propertyAccessor;
    }

    protected function persistPropertyAccessorHere()
    {
        return boolval($this->persistPropertyAccessorHere);
    }

    protected function setPersistPropertyAccessorHere(bool $persist)
    {
        $this->persistPropertyAccessorHere = $persist;
    }

    public function findPropertyAccessor()
    {
        $propertyAccessor = $this->propertyAccessor;
        if (null === $propertyAccessor && method_exists($this, 'getValueForMethod')) {
            $propertyAccessor = $this->getValueForMethod('getPropertyAccessor');
        }
        if (!$propertyAccessor instanceof PropertyAccessorInterface) {
            $propertyAccessor = $this->createPropertyAccessor();
        }
        if ($this->persistPropertyAccessorHere()) {
            $this->setPropertyAccessor($propertyAccessor);
        }
        return $propertyAccessor;
    }

    /** @SuppressWarnings(PHPMD.StaticAccess) */
    protected function createPropertyAccessor()
    {
        return PropertyAccess::createPropertyAccessor();
    }
}

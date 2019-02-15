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
        $propertyAccessor = $this->propertyAccessor;
        if (!$propertyAccessor instanceof PropertyAccessorInterface && $this->persistPropertyAccessorHere()) {
            $propertyAccessor = $this->createPropertyAccessor();
            $this->setPropertyAccessor($propertyAccessor);
        }
        return $propertyAccessor;
    }

    protected function setPropertyAccessor(?PropertyAccessorInterface $propertyAccessor)
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
        return $propertyAccessor ?: $this->createPropertyAccessor();
    }

    /** @SuppressWarnings(PHPMD.StaticAccess) */
    protected function createPropertyAccessor()
    {
        return PropertyAccess::createPropertyAccessor();
    }
}

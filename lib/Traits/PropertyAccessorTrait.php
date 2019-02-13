<?php

namespace JBJ\Workflow\Traits;

use Symfony\Component\PropertyAccess\PropertyAccessorInterface;
use Symfony\Component\PropertyAccess\PropertyAccess;

trait PropertyAccessorTrait
{
    private $propertyAccessor;

    protected function assertHasPropertyAccessor()
    {
        $propertyAccessor = $this->propertyAccessor;
        if (null === $propertyAccessor) {
            throw new \JBJ\Workflow\Exception\FixMeException('no property accessor');
        }
    }

    public function getPropertyAccessor()
    {
        $this->assertHasPropertyAccessor();
        return $this->propertyAccessor;
    }

    public function setPropertyAccessor(PropertyAccessorInterface $propertyAccessor)
    {
        $this->propertyAccessor = $propertyAccessor;
    }
}

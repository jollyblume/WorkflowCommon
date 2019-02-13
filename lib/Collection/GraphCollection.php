<?php

namespace JBJ\Workflow\Collection;

use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;

class GraphCollection implements ArrayCollectionInterface
{
    use GraphCollectionTrait;

    public function __construct(string $name, array $elements = [], array $rules = [], PropertyAccessorInterface $propertyAccessor = null)
    {
        $this->initializeTrait($name, $elements, $rules, $propertyAccessor);
    }
}

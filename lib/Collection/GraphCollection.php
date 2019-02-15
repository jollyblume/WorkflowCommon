<?php

namespace JBJ\Workflow\Collection;

use Symfony\Component\PropertyAccess\PropertyAccessorInterface;

/**
 * GraphCollection
 *
 * This is a reference implementation for classes using GraphCollectionTrait
 */
class GraphCollection implements ArrayCollectionInterface
{
    use GraphCollectionTrait;

    /**
     * Constructor
     *
     * Initializes the trait
     */
    public function __construct(string $name, array $elements = [], array $rules = [], PropertyAccessorInterface $propertyAccessor = null)
    {
        $this->initializeTrait($name, $elements, $rules, $propertyAccessor);
    }
}

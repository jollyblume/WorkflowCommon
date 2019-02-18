<?php

namespace JBJ\Workflow\Collection\Reference;

use JBJ\Workflow\Collection\ArrayCollectionInterface;
use JBJ\Workflow\Collection\GraphCollectionTrait;

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
    public function __construct(string $name, array $elements = [])
    {
        $this->setName($name);
        $this->saveElements($elements);
    }
}

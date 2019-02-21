<?php

namespace JBJ\Workflow\Collection\Reference;

use JBJ\Workflow\NodeCollectionInterface;
use JBJ\Workflow\Collection\NodeCollectionTrait;

/**
 * GraphCollection
 *
 * This is a reference implementation for classes using NodeCollectionTrait
 */
class GraphCollection implements NodeCollectionInterface
{
    use NodeCollectionTrait;

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

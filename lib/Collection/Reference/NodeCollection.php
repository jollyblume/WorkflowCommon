<?php

namespace JBJ\Workflow\Collection\Reference;

use JBJ\Workflow\NodeCollectionInterface;
use JBJ\Workflow\Collection\NodeCollectionTrait;

/**
 * NodeCollection
 *
 * This is a reference implementation for classes using NodeCollectionTrait
 */
class NodeCollection implements NodeCollectionInterface
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

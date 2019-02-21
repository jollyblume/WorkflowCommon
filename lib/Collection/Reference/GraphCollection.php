<?php

namespace JBJ\Workflow\Collection\Reference;

use JBJ\Workflow\NodeInterface;
use JBJ\Workflow\Collection\GraphCollectionTrait;

/**
 * GraphCollection
 *
 * This is a reference implementation for classes using GraphCollectionTrait
 */
class GraphCollection implements NodeInterface
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

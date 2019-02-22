<?php

namespace JBJ\Workflow\Collection\Reference;

use JBJ\Workflow\NodeCollectionInterface;
use JBJ\Workflow\Collection\NamedCollectionTrait;

/**
 * LeafCollection
 *
 * This is a reference implementation for classes using NamedCollectionTrait
 */
class LeafCollection implements NodeCollectionInterface
{
    use NamedCollectionTrait;

    /**
     * Constructor
     *
     * Sets the collection name.
     * Saves the children for later initialization
     */
    public function __construct(string $name, array $elements = [])
    {
        $this->setName($name);
        $this->saveElements($elements);
    }
}

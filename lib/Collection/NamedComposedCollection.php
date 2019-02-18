<?php

namespace JBJ\Workflow\Collection;

/**
 * NamedComposedCollection
 *
 * This is a reference implementation for classes using NamedCollectionTrait
 */
class NamedComposedCollection implements ArrayCollectionInterface
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

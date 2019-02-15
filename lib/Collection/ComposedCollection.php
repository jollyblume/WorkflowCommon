<?php

namespace JBJ\Workflow\Collection;

/**
 * ComposedCollection
 *
 * This is a reference implementation for classes using CollectionTrait
 */
class ComposedCollection implements ArrayCollectionInterface
{
    use CollectionTrait;

    /**
     * Constructor
     *
     * Saves the children for later initialization
     */
    public function __construct(array $elements = [])
    {
        $this->saveElements($elements);
    }
}

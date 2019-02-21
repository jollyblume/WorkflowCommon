<?php

namespace JBJ\Workflow\Collection\Reference;

use JBJ\Workflow\NodeInterface;
use JBJ\Workflow\Collection\CollectionTrait;

/**
 * ComposedCollection
 *
 * This is a reference implementation for classes using CollectionTrait
 */
class ComposedCollection implements NodeInterface
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

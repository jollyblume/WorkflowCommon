<?php

namespace JBJ\Workflow\Collection\Reference;

use JBJ\Workflow\ArrayCollectionInterface;
use JBJ\Workflow\Collection\CollectionTrait;

/**
 * Collection
 *
 * This is a reference implementation for classes using CollectionTrait
 */
class Collection implements ArrayCollectionInterface
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

<?php

namespace JBJ\Workflow\Collection\Reference;

use JBJ\Workflow\NodeCollectionInterface;
use JBJ\Workflow\Collection\NodeCollectionTrait;
use JBJ\Workflow\Traits\CreateIdTrait;

/**
 * UuidLeafCollection
 *
 * This is a reference implementation for classes using NodeCollectionTrait and
 * CreateIdTrait.
 */
class UuidLeafCollection implements NodeCollectionInterface
{
    use NodeCollectionTrait, CreateIdTrait;

    /**
     * Constructor
     *
     * Initializes the trait
     */
    public function __construct(string $name = '', array $elements = [])
    {
        $name = $this->createId($name);
        $this->setName($name);
        $this->saveElements($elements);
    }
}
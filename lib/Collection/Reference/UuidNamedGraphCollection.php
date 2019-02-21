<?php

namespace JBJ\Workflow\Collection\Reference;

use JBJ\Workflow\NodeInterface;
use JBJ\Workflow\Collection\GraphCollectionTrait;
use JBJ\Workflow\Traits\CreateIdTrait;

/**
 * GraphCollection
 *
 * This is a reference implementation for classes using GraphCollectionTrait
 */
class UuidNamedGraphCollection implements NodeInterface
{
    use GraphCollectionTrait, CreateIdTrait;

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

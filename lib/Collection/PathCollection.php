<?php

namespace JBJ\Workflow\Collection;

use JBJ\Workflow\NodeCollectionInterface;
use JBJ\Workflow\Collection\LeafCollectionTrait;

/**
 * PathCollection
 *
 * PathCollection maps a path to a node.
 *
 * The root path is always '/'and points to the node provided to the constructor.
 *
 * Paths for each node in the graph are relative to this root path/node.
 */
class PathCollection implements NodeCollectionInterface
{
    use LeafCollectionTrait;

    /**
     * Constructor
     *
     * Sets the collection name.
     * Saves the children for later initialization
     */
    public function __construct(NodeCollectionInterface $node)
    {
        $this->setName($node->getName());
        $this->saveElements(['/' => $node]);
    }
}

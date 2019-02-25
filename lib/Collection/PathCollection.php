<?php

namespace JBJ\Workflow\Collection;

use JBJ\Workflow\NodeCollectionInterface;
use JBJ\Workflow\Collection\LeafCollectionTrait;

/**
 * PathCollection
 *
 * PathCollection maps a path to a node.
 *
 * The root path is always '/'. The root path will point to the first node added
 * and paths for each node in the graph will be relative to this root path/node.
 *
 * A parent path is the root node's path in some larger graph.
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
    public function __construct(string $parentPath)
    {
        $this->setName($parentPath);
        $this->saveElements([]);
    }
}

<?php

namespace JBJ\Workflow\Collection;

use JBJ\Workflow\Traits\ElementNameTrait;
use JBJ\Workflow\Traits\ElementParentTrait;
use JBJ\Workflow\Traits\VisiteeTrait;

/**
 * LeafCollectionTrait
 *
 * Creates a collection that can be a graph leaf node
 */
trait LeafCollectionTrait
{
    use CollectionTrait, ElementNameTrait, ElementParentTrait, VisiteeTrait;

    public function isLeafNode()
    {
        return true;
    }
}

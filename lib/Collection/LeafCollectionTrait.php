<?php

namespace JBJ\Workflow\Collection;

use JBJ\Workflow\Traits\ElementNameTrait;
use JBJ\Workflow\Traits\ElementParentTrait;

/**
 * LeafCollectionTrait
 *
 * Creates a collection that can be a graph leaf node
 */
trait LeafCollectionTrait
{
    use CollectionTrait, ElementNameTrait, ElementParentTrait;
}

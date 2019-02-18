<?php

namespace JBJ\Workflow\Collection;

use JBJ\Workflow\Traits\ElementNameTrait;
use JBJ\Workflow\Traits\ElementParentTrait;

/**
 * NamedCollectionTrait
 *
 * Creates a collection that can can a graph leaf node
 */
trait NamedCollectionTrait
{
    use CollectionTrait, ElementNameTrait, ElementParentTrait;
}

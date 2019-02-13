<?php

namespace JBJ\Workflow\Collection;

class ComposedCollection implements ArrayCollectionInterface
{
    use CollectionTrait;

    public function __construct(array $elements = [])
    {
        if (!empty($elements)) {
            $this->setChildren($elements);
        }
    }
}

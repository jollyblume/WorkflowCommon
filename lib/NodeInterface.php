<?php

namespace JBJ\Workflow;

use JBJ\Workflow\Collection\ArrayCollectionInterface;

interface NodeInterface extends ArrayCollectionInterface
{
    public function getName();
    public function getParent();
    public function setParent($parent);
}

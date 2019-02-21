<?php

namespace JBJ\Workflow;

interface NodeInterface extends ArrayCollectionInterface
{
    public function getName();
    public function getParent();
    public function setParent($parent);
}

<?php

namespace JBJ\Workflow;

use JBJ\Workflow\Visitor\NodeVisitorInterface;

interface NodeInterface extends ArrayCollectionInterface
{
    public function getName();
    public function getParent();
    public function setParent($parent);
    public function isLeafNode();
    public function accept(NodeVisitorInterface $visitor);
}

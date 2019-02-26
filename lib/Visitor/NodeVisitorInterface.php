<?php

namespace JBJ\Workflow\Visitor;

use JBJ\Workflow\NodeCollectionInterface;

interface NodeVisitorInterface
{
    public function visit(NodeCollectionInterface $node, string $nodePath): bool;
}

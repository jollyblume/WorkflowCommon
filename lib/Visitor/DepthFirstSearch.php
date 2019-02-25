<?php

namespace JBJ\Workflow\Visitor;

use JBJ\Workflow\Visitor\NodeVisitorInterface;
use JBJ\Workflow\NodeCollectionInterface;

class DepthFirstSearch
{
    protected function innerTraverse(NodeCollectionInterface $node, NodeVisitorInterface $visitor, string $currentPath)
    {
        $paths = [$currentPath => 1];
        $node->accept($visitor);
        if (!$node->isLeafNode()) {
            $currentPath = rtrim($currentPath, '/');
            foreach ($node as $child) {
                $childPath = sprintf('%s/%s', $currentPath, $child->getName());
                $innerPaths = $this->traverse($child, $visitor, $childPath);
                $paths = array_merge($paths, $innerPaths);
            }
        }
        return $paths;
    }

    public function traverse(NodeCollectionInterface $node, NodeVisitorInterface $visitor)
    {
        return $this->innerTraverse($node, $visitor, '/');
    }
}

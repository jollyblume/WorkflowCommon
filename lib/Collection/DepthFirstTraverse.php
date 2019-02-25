<?php

namespace JBJ\Workflow\Collection;

use JBJ\Workflow\Visitor\NodeVisitorInterface;
use JBJ\Workflow\NodeCollectionInterface;

class DepthFirstTraverse
{
    protected function innerTraverse(NodeCollectionInterface $node, ?NodeVisitorInterface $visitor, string $currentPath)
    {
        $paths = [$currentPath => 1];
        if ($visitor) {
            $node->accept($visitor);
        }
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

    public function traverse(NodeCollectionInterface $node, NodeVisitorInterface $visitor, string $currentPath = '/')
    {
        return $this->innerTraverse($node, $visitor, $paths);
    }
}

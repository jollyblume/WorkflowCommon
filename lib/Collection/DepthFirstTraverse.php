<?php

namespace JBJ\Workflow\Collection;

use JBJ\Workflow\Visitor\NodeVisitorInterface;
use JBJ\Workflow\NodeCollectionInterface;

class DepthFirstTraverse
{
    protected function innerTraverse(PathCollection $paths, string currentPath, NodeVisitorInterface $visitor = null)
    {
        if ($visitor) {
            $node = $paths[$currentPath];
            $node->accept($visitor);
        }
        if (!$node->isLeafNode()) {
            $currentPath = rtrim($currentPath, '/');
            foreach ($node as $child) {
                $childPath = sprintf('%s/%s', $currentPath, $child->getName());
                $paths[$childPath] = $child;
                $this->innerTraverse($paths, $childPath, $visitor);
            }
        }
    }

    public function traverse(NodeCollectionInterface $node, NodeVisitorInterface $visitor = null)
    {
        $paths = new PathCollection($node);
        $this->innerTraverse($paths, '/', $visitor);
        return $paths;
    }
}

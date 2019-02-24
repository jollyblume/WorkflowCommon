<?php

namespace JBJ\Workflow\Visitor;

use JBJ\Workflow\Visitor\NodeVisitorInterface;
use JBJ\Workflow\NodeCollectionInterface;

class DepthFirstSearch implements NodeVisitorInterface
{
    private $visited = [];
    private $visitor;
    private $currentPath;

    public function visit(NodeCollectionInterface $node)
    {
        $currentPath = $this->currentPath;
        $visited = boolval($this->visited[$currentPath]);
        if (!$visited) {
            $visitor = $this->visitor;
            $visitor->visit($node);
        }
        $this->visited[$currentPath]++;
    }

    public function innerTraverse(NodeCollectionInterface $node, NodeVisitorInterface $visitor, string $currentPath)
    {
        $this->visitor = $visitor;
        $this->visited[$currentPath] = 0;
        $this->currentPath = $currentPath;
        $node->accept($this);
        if (!$node->isLeafNode()) {
            $currentPath = rtrim($currentPath, '/');
            foreach ($node as $child) {
                $childPath = sprintf('/%s/%s', $currentPath, $child->getName());
                $paths = $this->traverse($child, $visitor, $childPath);
                $this->visited = array_merge($this->visited, $paths);
            }
        }
        return $this->visited;
    }

    public function traverse(NodeCollectionInterface $node, NodeVisitorInterface $visitor)
    {
        return $this->innerTraverse($node, $visitor, '/');
    }
}

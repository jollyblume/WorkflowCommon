<?php

namespace JBJ\Workflow\Traverse;

use JBJ\Workflow\Visitor\NodeVisitorInterface;
use JBJ\Workflow\NodeCollectionInterface;

class DepthFirstTraverse
{
    private $visitor;
    private $paths;

    protected function innerTraverse(NodeCollectionInterface $node, string $nodePath)
    {
        $visitor = $this->visitor;
        $visited = null === $visitor;
        if ($visitor) {
            $visited = $node->accept($visitor);
        }
        $this->paths[$nodePath] = $visited ? $node : false;
        if ($visited && !$node->isLeafNode()) {
            $currentPath = rtrim($nodePath, '/');
            foreach ($node as $child) {
                $childPath = sprintf('%s/%s', $currentPath, $child->getName());
                $this->innerTraverse($child, $childPath);
            }
        }
    }

    public function traverse(NodeCollectionInterface $node, NodeVisitorInterface $visitor = null, string $pathsName = 'visitedNodes')
    {
        $this->visitor = $visitor;
        $this->paths = new PathCollection($pathsName);
        $this->innerTraverse($node, '/');
        return $this->paths;
    }
}

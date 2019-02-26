<?php

namespace JBJ\Workflow\Traverser;

use JBJ\Workflow\Visitor\NodeVisitorInterface;
use JBJ\Workflow\Collection\PathCollection;

class PathTraverser
{
    public function traverse(PathCollection $paths, NodeVisitorInterface $visitor, string $pathsName = 'visitedNodes')
    {
        $visitedPaths = new PathCollection($pathsName);
        foreach ($paths as $nodePath => $node) {
            $visited = $visitor->visit($node, $nodePath);
            if ($visited) {
                $visitedPaths[$nodePath] = $node;
            }
        }
        return $visitedPaths;
    }
}

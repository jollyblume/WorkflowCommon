<?php

namespace JBJ\Workflow\Traverser;

use JBJ\Workflow\Visitor\NodeVisitorInterface;
use JBJ\Workflow\Collection\PathCollection;

class PathTraverser
{
    public function traverse(PathCollection $paths, NodeVisitorInterface $visitor, string $pathsName = 'visitedNodes')
    {
        $visitedPaths = new PathCollection($pathsName);
        foreach ($paths as $path => $node) {
            if (false === $node) {
                continue;
            }
            $visited = $node->accept($visitor);
            $visitedPaths[$path] = $visited ? $node : false;
        }
        return $visitedPaths;
}

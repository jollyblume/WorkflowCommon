<?php

namespace JBJ\Workflow\Traverse;

use JBJ\Workflow\Visitor\NodeVisitorInterface;

class PathTraverse
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

<?php

namespace JBJ\Workflow\Collection;

use JBJ\Workflow\Visitor\NodeVisitorInterface;

class PathTraverse
{
    public function traverse(PathCollection $paths, NodeVisitorInterface $visitor)
    {
        foreach ($paths as $node) {
            $node->accept($visitor);
        }
    }
}

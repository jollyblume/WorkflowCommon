<?php

namespace JBJ\Workflow\Traverse;

use Closure;
use JBJ\Workflow\Visitor\NodeVisitorInterface;

class ClosureTraverse
{
    public function traverse(PathCollection $paths, Closure $nodeFunction, string $pathsName = 'answers')
    {
        $answers = new PathCollection($pathsName);
        foreach ($paths as $path => $node) {
            if (false === $node) {
                continue;
            }
            $answer = $nodeFunction($node, $path);
            $answers[$path] = $answer;
        }
        return $answers;
}

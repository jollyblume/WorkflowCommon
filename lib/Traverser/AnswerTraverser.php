<?php

namespace JBJ\Workflow\Traverser;

use Closure;
use JBJ\Workflow\Collection\PathCollection;

class AnswerTraverser
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

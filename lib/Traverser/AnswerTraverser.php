<?php

namespace JBJ\Workflow\Traverser;

use Closure;
use JBJ\Workflow\Collection\PathCollection;

class AnswerTraverser
{
    public function traverse(PathCollection $paths, Closure $nodeFunction, string $pathsName = 'answers')
    {
        $answers = new PathCollection($pathsName);
        foreach ($paths as $nodePath => $node) {
            if (false === $node) {
                continue;
            }
            $answer = $nodeFunction($node, $nodePath);
            if ($answer) {
                $answers[$nodePath] = $answer;
            }
        }
        return $answers;
    }
}

<?php

namespace JBJ\Workflow\Tests\Traverser;

use JBJ\Workflow\Traverser\DepthFirstTraverser;
use JBJ\Workflow\Traverser\PathTraverser;
use JBJ\Workflow\Traverser\AnswerTraverser;
use JBJ\Workflow\NodeVisitorInterface;
use JBJ\Workflow\NodeCollectionInterface;

class TraverserTest extends TraverserBaseTest
{
    protected function getPaths(NodeCollectionInterface $graph, $visitor = null, string $pathName = 'visitedNodes')
    {
        $traverser = new DepthFirstTraverser();
        $paths = $traverser->traverse($graph, $visitor, $pathName);
        return $paths;
    }

    public function testDepthFirstTraverser()
    {
        $graph = $this->buildGraph('dfs-test');
        $visitor = $this->getLeafDetectorVisitor();
        $paths = $this->getPaths($graph, $visitor);
        $this->assertCount(20, $paths);
        $expectedPaths = array_keys($this->buildGraphPaths($graph));
        $this->assertEquals($expectedPaths, $paths->getKeys());
        $this->assertCount(10, $visitor->getLeafNodes());
    }

    public function testDepthFirstTraverserIgnoresFalse()
    {
        $graph = $this->buildGraph('dfs-test');
        $visitor = $this->getIgnoreLeafNodesVisitor();
        $paths = $this->getPaths($graph, $visitor);
        $this->assertCount(10, $paths);
        $this->assertCount(10, $visitor->getIgnoredNodes());
        $allPaths = $this->getPaths($graph);
        foreach ($visitor->getIgnoredNodes() as $nodePath => $node) {
            $paths[$nodePath] = $node;
        }
        $this->assertEquals($allPaths, $paths);
    }

    public function testPathTraverser()
    {
        $graph = $this->buildGraph('dfs-test');
        $expectedLeafDetector = $this->getLeafDetectorVisitor();
        $expectedPaths = $this->getPaths($graph, $expectedLeafDetector);
        $pathTraverser = new PathTraverser();
        $leafDetector = $this->getLeafDetectorVisitor();
        $paths = $pathTraverser->traverse($expectedPaths, $leafDetector);
        $this->assertEquals($expectedLeafDetector->getLeafNodes(), $leafDetector->getLeafNodes());
        $this->assertEquals($expectedPaths, $paths);
    }

    public function testAnswerTraverser()
    {
        $graph = $this->buildGraph('dfs-test');
        $leafDetector = $this->getLeafDetectorVisitor();
        $paths = $this->getPaths($graph, $leafDetector);
        $expectedLeafNodes = array_keys($leafDetector->getLeafNodes());
        $answerTraverser = new AnswerTraverser();
        $answers = $answerTraverser->traverse($paths, function (NodeCollectionInterface $node, string $nodePath) {
            if ($node->isLeafNode()) {
                return $nodePath;
            }
            return null;
        });
        $this->assertEquals($expectedLeafNodes, $answers->getKeys());
        $this->assertEquals($expectedLeafNodes, $answers->getValues());
    }
}

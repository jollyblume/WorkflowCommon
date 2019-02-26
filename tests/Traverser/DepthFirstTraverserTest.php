<?php

namespace JBJ\Workflow\Tests\Traverser;

use JBJ\Workflow\Traverser\DepthFirstTraverser;

class DepthFirstTraverserTest extends TraverserBaseTest
{
    public function testDepthFirstTraverser()
    {
        $graph = $this->buildGraph('dfs-test');
        $visitor = $this->getLeafDetectorVisitor();
        $traverser = new DepthFirstTraverser();
        $paths = $traverser->traverse($graph, $visitor, 'visited');
        $expectedPaths = array_keys($this->buildGraphPaths($graph));
        $this->assertEquals($expectedPaths, $paths->getKeys());
        $this->assertCount(10, $visitor->getLeafNodes());
    }
}

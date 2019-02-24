<?php

namespace JBJ\Workflow\Tests\Visitor;

use PHPUnit\Framework\TestCase;
use JBJ\Workflow\Collection\Reference\NodeCollection;
use JBJ\Workflow\Collection\Reference\LeafCollection;
use JBJ\Workflow\Visitor\NodeVisitorInterface;
use JBJ\Workflow\Visitor\DepthFirstSearch;
use JBJ\Workflow\NodeInterface;
use JBJ\Workflow\NodeCollectionInterface;

class VisitorTest extends TestCase
{
    protected function getGraph()
    {
        $graph = new NodeCollection('graph');
        foreach (['node-1', 'node-2', 'node-3'] as $nodename) {
            $graph[] = new NodeCollection($nodename);
            $graph[$nodename][] = new LeafCollection('leaf-1');
        }
        $graph[] = new LeafCollection('leaf-1');
        return $graph;
    }

    protected function getGraphPaths(NodeCollectionInterface $graph)
    {
        $paths = $this->innerGraphPaths($graph, '/');
        return $paths;
    }

    private function innerGraphPaths(NodeCollectionInterface $node, string $currentPath)
    {
        $paths = [$currentPath => 1];
        if (!$node->isLeafNode()) {
            $currentPath = rtrim($currentPath, '/');
            foreach ($node as $child) {
                $childPath = sprintf('%s/%s', $currentPath, $child->getName());
                $newPaths = $this->innerGraphPaths($child, $childPath);
                $paths = array_merge($paths, $newPaths);
            }
        }
        return $paths;
    }

    protected function getLeafDetectorVisitor()
    {
        $visitor = new class() implements NodeVisitorInterface {
            private $leafNodes = [];
            public function visit(NodeCollectionInterface $node)
            {
                if ($node->isLeafNode()) {
                    $nodename = $node->getName();
                    $leafNodes = $this->leafNodes;
                    if (!array_key_exists($nodename, $leafNodes)) {
                        $leafNodes[$nodename] = 0;
                    }
                    $leafNodes[$nodename]++;
                    $this->leafNodes = $leafNodes;
                }
            }
            public function clear()
            {
                $this->leafNodes = [];
            }
            public function getLeafNodes()
            {
                $leafNodes = $this->leafNodes;
                return $leafNodes;
            }
        };
        return $visitor;
    }

    protected function traverseGraph(NodeCollectionInterface $node, NodeVisitorInterface $visitor)
    {
        $node->accept($visitor);
        if (!$node->isLeafNode()) {
            foreach ($node as $child) {
                $this->traverseGraph($child, $visitor);
            }
        }
    }

    public function testVisitWorkflow()
    {
        $graph = $this->getGraph();
        $visitor = $this->getLeafDetectorVisitor();
        $this->assertEquals([], $visitor->getLeafNodes());
        $this->assertTrue(method_exists($graph, 'accept'));
        $graph->accept($visitor); // test just a node
        $this->assertEquals([], $visitor->getLeafNodes());
        $graph['node-1']['leaf-1']->accept($visitor);
        $this->assertEquals(['leaf-1' => 1], $visitor->getLeafNodes());
        $visitor->clear();
        $this->assertEquals([], $visitor->getLeafNodes());
        $this->traverseGraph($graph, $visitor);
        $expected = [
            'leaf-1' => 4,
        ];
        $this->assertEquals($expected, $visitor->getLeafNodes());
    }

    protected function getNullVisitor()
    {
        $visitor = new class() implements NodeVisitorInterface {
            public function visit(NodeCollectionInterface $node)
            {
                $node;
            }
        };
        return $visitor;
    }

    public function testDepthFirstSearch()
    {
        $dfs = new DepthFirstSearch();
        $graph = $this->getGraph();
        $visitor = $this->getNullVisitor();
        $paths = $dfs->traverse($graph, $visitor);
        $expected = $this->getGraphPaths($graph);
        $this->assertEquals($this->getGraphPaths($graph), $paths);
    }
}

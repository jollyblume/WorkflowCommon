<?php

namespace JBJ\Workflow\Tests\Visitor;

use JBJ\Workflow\Collection\Reference\NodeCollection;
use JBJ\Workflow\Collection\Reference\LeafCollection;
use JBJ\Workflow\Visitor\NodeVisitorInterface;
use JBJ\Workflow\Visitor\DepthFirstSearch;
use JBJ\Workflow\NodeInterface;
use JBJ\Workflow\NodeCollectionInterface;
use PHPUnit\Framework\TestCase;

class TraverserBaseTest extends TestCase
{
    protected function buildGraph(string $name)
    {
        $graph = new NodeCollection($name);
        foreach (['node-1', 'node-2', 'node-3'] as $nodename) {
            $graph[] = new NodeCollection($nodename);
            foreach (['subnode-1', 'subnode-2'] as $subNodename) {
                $graph[$nodename][] = new NodeCollection($subNodename);
                $graph[$nodename][$subNodename][] = new LeafCollection('property');
            }
            $graph[$nodename][] = new LeafCollection('property');
        }
        $graph[] = new LeafCollection('property');
        return $graph;
    }

    protected function buildGraphPaths(NodeCollectionInterface $graph)
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

    public function testBaseGraph()
    {
        $graph = $this->buildGraph('base-graph');
        $paths = $this->buildGraphPaths($graph);
        $expectedPaths = [
            '/',
            '/node-1',
            '/node-1/subnode-1',
            '/node-1/subnode-1/property',
            '/node-1/subnode-2',
            '/node-1/subnode-2/property',
            '/node-1/property',
            '/node-2',
            '/node-2/subnode-1',
            '/node-2/subnode-1/property',
            '/node-2/subnode-2',
            '/node-2/subnode-2/property',
            '/node-2/property',
            '/node-3',
            '/node-3/subnode-1',
            '/node-3/subnode-1/property',
            '/node-3/subnode-2',
            '/node-3/subnode-2/property',
            '/node-3/property',
            '/property',
        ];
        $this->assertEquals($expectedPaths, array_keys($paths));
    }

    protected function getLeafDetectorVisitor()
    {
        $visitor = new class() implements NodeVisitorInterface {
            private $leafNodes = [];
            public function visit(NodeCollectionInterface $node, string $nodePath)
            {
                if ($node->isLeafNode()) {
                    $leafNodes = $this->leafNodes;
                    if (!array_key_exists($nodePath, $leafNodes)) {
                        $leafNodes[$nodePath] = 0;
                    }
                    $leafNodes[$nodePath]++;
                    $this->leafNodes = $leafNodes;
                }
                return true;
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

    protected function getNullVisitor()
    {
        $visitor = new class() implements NodeVisitorInterface {
            public function visit(NodeCollectionInterface $node, string $nodePath)
            {
                $node;
                $nodePath;
                return true;
            }
        };
        return $visitor;
    }
}

<?php

namespace JBJ\Workflow\Tests\Collection;

use JBJ\Workflow\Collection\Reference\NodeCollection;
use JBJ\Workflow\Collection\Reference\LeafCollection;
use JBJ\Workflow\Visitor\NodeVisitorInterface;
use JBJ\Workflow\NodeCollectionInterface;
use PHPUnit\Framework\TestCase;

class CloneTest extends TestCase
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
        $paths = [$currentPath => $node];
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

    protected function getIgnoreLeafNodesVisitor()
    {
        $visitor = new class() implements NodeVisitorInterface {
            private $ignoredNodes = [];
            public function visit(NodeCollectionInterface $node, string $nodePath): bool
            {
                if ($node->isLeafNode()) {
                    $this->ignoredNodes[$nodePath] = $node;
                    return false;
                }
                return true;
            }
            public function clear()
            {
                $this->ignoredNodes = [];
            }
            public function getIgnoredNodes()
            {
                $ignoredNodes = $this->ignoredNodes;
                return $ignoredNodes;
            }
        };
        return $visitor;
    }

    public function testCone()
    {
        $graph = $this->buildGraph('test-graph');
        $clone = clone $graph;
        $this->assertEquals($graph->getName(), $clone->getName());
        $graphPaths = $this->buildGraphPaths($graph);
        $clonePaths = $this->buildGraphPaths($clone);
        $this->assertEquals($graphPaths, $clonePaths);
        foreach ($clonePaths as $nodePath => $clonedNode) {
            if ($clonedNode->isLeafNode()) {
                continue;
            }
            $node = $graphPaths[$nodePath];
            $this->assertEquals($node->getName(), $clonedNode->getName());
            if ($nodePath !== '/') {
                $this->assertNotEquals($node->getParent(), $clonedNode->getParent());
                $this->assertNotNull($clonedNode->getParent());
            }
            $node[] = new LeafCollection('additional.leaf');
            $this->assertFalse($clonedNode->containsKey('additional.leaf'));
        }
    }
}

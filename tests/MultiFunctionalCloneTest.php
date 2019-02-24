<?php

namespace JBJ\Workflow\Tests;

use JBJ\Workflow\Collection\Reference\Collection;
use JBJ\Workflow\Collection\Reference\LeafCollection;
use JBJ\Workflow\Collection\Reference\NodeCollection;
use PHPUnit\Framework\TestCase;

class MultiFunctionalCloneTest extends TestCase
{
    protected function createGraph(string $name)
    {
        $graph = new NodeCollection($name);
        $graph[] = new NodeCollection('bag');
        $graph['bag'][] = new LeafCollection('bag.leaf');
        $graph['bag']['bag.leaf']['collection'] = new Collection(); //external ref
        $graph[] = new LeafCollection('root.leaf');
        $graph['root.leaf']['root.graph'] = $graph; //circular dependancy
        return $graph;
    }

    public function testBasicClone()
    {
        $graph = $this->createGraph('graph');
        $clone = clone $graph;
        $this->assertEquals($graph['bag']['bag.leaf']['collection'], $clone['bag']['bag.leaf']['collection']);
        $this->assertEquals($graph, $clone['root.leaf']['root.graph']);
        $this->assertEquals($graph, $clone);
        $graph['bag'][] = new LeafCollection('after.leaf');
        $this->assertFalse($clone['bag']->containsKey('after.leaf'));
        $graph['bag']['bag.leaf']['this.is.new'] = true;
        $this->assertFalse($clone['bag']['bag.leaf']->containsKey('this.is.new'));
        $this->assertNotEquals($graph, $clone);
    }
}

<?php

namespace JBJ\Workflow\Tests\Collection;

use JBJ\Workflow\Collection\ArrayCollectionInterface;
use JBJ\Workflow\Collection\NamedCollectionTrait;
use JBJ\Workflow\Collection\GraphCollectionTrait;
use PHPUnit\Framework\TestCase;

class GraphCollectionCloneTest extends TestCase
{
    protected function createGraphCollection(string $name = '', array $elements = [])
    {
        $graphCollection = new class($name, $elements) implements ArrayCollectionInterface {
            use GraphCollectionTrait;
            public function __construct(string $name = '', array $elements = [])
            {
                $this->setName($name);
                $this->saveElements($elements);
            }
        };
        return $graphCollection;
    }

    protected function createCollection(string $name, array $elements = [])
    {
        $collection = new class($name, $elements) implements ArrayCollectionInterface {
            use NamedCollectionTrait;
            public function __construct(string $name, array $elements = [])
            {
                $this->setName($name);
                $this->saveElements($elements);
            }
        };
        return $collection;
    }

    public function testBasicClone()
    {
        $elements = [
            $this->createGraphCollection('node-1', [
                $this->createCollection('child-1'),
            ]),
        ];
        $rootNode = $this->createGraphCollection('root-node', $elements);
        $clonedRoot = clone $rootNode;
        $rootNode[] = $this->createGraphCollection('node-root');
        foreach ($rootNode->getKeys() as $nodename) {
            $rootNode[$nodename][] = $this->createCollection('child-root');
        }
        $clonedRoot[] = $this->createGraphCollection('node-clone');
        foreach ($clonedRoot->getKeys() as $nodename) {
            $clonedRoot[$nodename][] = $this->createCollection('child-clone');
        }
        $rootKeys = [
            'node-1',
            'node-root',
        ];
        $cloneKeys = [
            'node-1',
            'node-clone',
        ];
        $this->assertEquals($rootKeys, $rootNode->getKeys());
        $this->assertEquals($cloneKeys, $clonedRoot->getKeys());
    }

    public function testLeafReferencesDoNotChange()
    {
        $reference = new class() {
        };
        $elements = [
            $this->createGraphCollection('node-1', [
                $this->createCollection('child-1', [
                    $reference,
                ]),
            ]),
        ];
        $rootNode = $this->createGraphCollection('root-node', $elements);
        $clonedRoot = clone $rootNode;
        $this->assertEquals($reference, $clonedRoot['node-1']['child-1']->first());
    }
}

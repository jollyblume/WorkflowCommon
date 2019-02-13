<?php

namespace JBJ\Workflow\Tests\Traits;

use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use JBJ\Workflow\Traits\EventDispatcherTrait;
use PHPUnit\Framework\TestCase;

class EventDispatcherTraitTest extends TestCase
{
    protected function getTrait()
    {
        $trait = new class() {
            use EventDispatcherTrait;
        };
        return $trait;
    }

    /** @expectedException \JBJ\Workflow\Exception\FixMeException */
    public function testEarlyGetThrowsException()
    {
        $trait = $this->getTrait();
        $trait->getDispatcher();
    }

    public function testGetReturnsSetValue()
    {
        $trait = $this->getTrait();
        $dispatcher = $this->getMockBuilder(EventDispatcherInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $trait->setDispatcher($dispatcher);
        $this->assertEquals($dispatcher, $trait->getDispatcher());
    }
}

<?php

namespace JBJ\Workflow\Traits;

use Symfony\Component\EventDispatcher\EventDispatcherInterface;

trait EventDispatcherTrait
{
    private $dispatcher;

    protected function assertHasDispatcher()
    {
        $dispatcher = $this->dispatcher;
        if (null === $dispatcher) {
            throw new \JBJ\Workflow\Exception\FixMeException('no dispatcher');
        }
    }

    public function getDispatcher()
    {
        $this->assertHasDispatcher();
        return $this->dispatcher;
    }

    public function setDispatcher(EventDispatcherInterface $dispatcher)
    {
        $this->dispatcher = $dispatcher;
    }
}

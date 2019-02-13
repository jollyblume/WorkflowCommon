<?php

namespace JBJ\Workflow\Traits;

use Symfony\Component\EventDispatcher\EventDispatcherInterface;

trait EventDispatcherTrait
{
    private $dispatcher;

    public function getDispatcher()
    {
        return $this->dispatcher;
    }

    protected function setDispatcher(EventDispatcherInterface $dispatcher)
    {
        $this->dispatcher = $dispatcher;
    }
}

<?php

namespace JBJ\Workflow\Traits;

trait ExpectedInterfaceTrait
{
    private $expectedInterfaces = [];

    protected function getExpectedInterfaces()
    {
        return $this->expectedInterfaces;
    }

    protected function setExpectedInterfaces(array $expectedInterfaces)
    {
        $this->expectedInterfaces = $expectedInterfaces;
    }

    protected function hasExpectedInterface($interface)
    {
        if (!is_object($interface)) {
            $expected = $this->getExpectedInterfaces();
            return in_array($interface, $expected);
        }
    }


    protected function assertExpectedInterface($interface)
    {
        $hasInterface = $this->hasExpectedInterface($interface);
        if (!$hasInterface) {
            throw new JBJ\Workflow\Exception\FixMeException('Invalid interface');
        }
    }
}

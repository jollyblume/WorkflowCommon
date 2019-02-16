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

    protected function hasExpectedInterface($interfaceorobject)
    {
        $classname =  is_object($interfaceorobject) ? get_class($interfaceorobject) : $interfaceorobject;
        $interfaces = class_implements($classname);
        $expectedInterfaces = $this->getExpectedInterfaces();
        $diff = array_diff($expectedInterfaces, $interfaces);
        return empty($diff);
    }


    protected function assertExpectedInterface($interface)
    {
        $hasInterface = $this->hasExpectedInterface($interface);
        if (!$hasInterface) {
            throw new \JBJ\Workflow\Exception\FixMeException('Invalid interface');
        }
    }
}

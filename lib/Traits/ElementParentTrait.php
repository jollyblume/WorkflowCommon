<?php

namespace JBJ\Workflow\Traits;

use Closure;

trait ElementParentTrait
{
    private $parent;

    public function getParent()
    {
        return $this->parent;
    }

    public function setParent($parent)
    {
        $this->parent = $parent;
    }

    public function recurseParents(string $method, Closure $getterLogic)
    {
        $value = $getterLogic($this, $method);
        $parent = $this->getParent();
        if (null !== $value || null === $parent || !method_exists($parent, 'recurseParents')) {
            return $value;
        }
        return $parent->recurseParents($method, $getterLogic);
    }

    public function getValueForMethod(string $method)
    {
        $getterLogic = function ($object, $method) {
            if (!method_exists($object, $method)) {
                return null;
            }
            return $object->$method();
        };
        return $this->recurseParents($method, $getterLogic);
    }

    public function hasValueForMethod(string $method)
    {
        $value = $this->getValueForMethod($method);
        return null !== $value;
    }

    public function getParentForValue(string $method, $expectedValue)
    {
        $getterLogic = function ($object, $method) use ($expectedValue) {
            if (!method_exists($object, $method)) {
                return null;
            }
            $value = $object->$method();
            return $expectedValue === $value ? $object : null;
        };
        return $this->recurseParents($method, $getterLogic);
    }

    public function getRootParent()
    {
        $getterLogic = function ($object, $method) {
            // $method should be 'getParent'
            $parent = $object->$method();
            if (null === $parent || !method_exists($parent, 'recurseParents')) {
                return $object;
            }
            return null;
        };
        return $this->recurseParents('getParent', $getterLogic);
    }
}

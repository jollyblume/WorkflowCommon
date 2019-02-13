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

    public function getValueForMethod(string $method, Closure $getterLogic = null)
    {
        if (null === $getterLogic) {
            $getterLogic = function ($object, $method) {
                if (!method_exists($object, $method)) {
                    return null;
                }
                return $object->$method();
            };
        }
        $value = $getterLogic($this, $method);
        $parent = $this->getParent();
        if (null !== $value || null === $parent || !method_exists($parent, 'getValueForMethod')) {
            return $value;
        }
        return $parent->getValueForMethod($method, $getterLogic);
    }
}

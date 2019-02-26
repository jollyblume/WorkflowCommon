<?php

namespace JBJ\Workflow\Collection;

/**
 * forked from doctrine/collections
 */

use Doctrine\Common\Collections\ArrayCollection;
use JBJ\Workflow\Traits\ElementNameTrait;
use JBJ\Workflow\Traits\ElementParentTrait;
use JBJ\Workflow\Exception\FixMeException;

/**
 * NodeCollectionTrait
 */
trait NodeCollectionTrait
{
    use CollectionCommonTrait, ElementNameTrait, ElementParentTrait;

    public function __clone()
    {
        $this->setParent(null);
        $children = $this->children;
        $newChildren = [];
        foreach ($children as $key => $value) {
            $newValue = clone $value;
            $newValue->getParent(null);
            $newChildren[$key] = clone $value;
        }
        $this->children = $newChildren;
    }

    public function isLeafNode()
    {
        return false;
    }

    protected function assertValidElement($element)
    {
        $throwThis = [];
        foreach (['getName', 'getParent', 'setParent'] as $method) {
            if (!method_exists($element, $method)) {
                $throwThis[] = $method;
            }
        }
        if (!empty($throwThis)) {
            throw new FixMeException(sprintf('Invalid element "%s". It is missing methods "%s"', join(',', $element), join(',', $throwThis)));
        }
    }

    /**
     * Set an element
     *
     * @param mixed $key
     * @param object $element
     */
    public function set($key, $element)
    {
        $this->assertValidElement($element);
        $newKey = $element->getName();
        if (is_string($key) && $key !== $newKey) {
            throw new FixMeException(sprintf('Invalid key "%s", expecting "%s".', $key, $newKey));
        }
        $children = $this->getChildren();
        if (null !== $element->getParent()) {
            throw new \JBJ\Workflow\Exception\FixMeException(sprintf('Unable to set "%s" while attached to "%s"', $element->getName(), get_class($element->getParent())));
        }
        $children[$newKey] = $element;
        $element->setParent($this);
    }

    /**
     * Add an element
     *
     * Gets the key from the element and calls set
     *
     * @param object $element
     */
    public function add($element)
    {
        return $this->set(null, $element);
    }

    /**
     * Remove an element with it's key
     *
     * Set's the element's parent to null.
     *
     * @param string $key
     * @return object Element removed
     */
    public function remove($key)
    {
        $children = $this->getChildren();
        $element = $children->remove($key);
        if ($element) {
            $element->setParent(null);
        }
        return $element;
    }

    /**
     * Remove an element with it's key
     *
     * Set's the element's parent to null.
     *
     * @param object $element
     * @return bool is successful
     */
    public function removeElement($element)
    {
        $children = $this->getChildren();
        $success = $children->removeElement($element);
        if ($success) {
            $element->setParent(null);
        }
        return $success;
    }
}

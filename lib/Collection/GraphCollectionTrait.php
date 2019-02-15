<?php

namespace JBJ\Workflow\Collection;

/**
 * forked from doctrine/collections
 */

use JBJ\Workflow\Traits\ElementNameTrait;
use JBJ\Workflow\Traits\ElementParentTrait;
use JBJ\Workflow\Exception\FixMeException;

trait GraphCollectionTrait
{
    use CollectionCommonTrait, ElementNameTrait, ElementParentTrait;

    protected function assertValidElement($element)
    {
        $throwThis = [];
        foreach (['getName', 'getParent', 'setParent'] as $method) {
            if (!method_exists($element, $method)) {
                $throwThis[] = $method;
            }
        }
        if (!empty($throwThis)) {
            throw new FixMeException(sprintf('Invalid element "%s". Expected methods missing "%s"', get_class($element), join(',', $throwThis)));
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
            throw new FixMeException(sprintf('Provided key "%s" must match $element->getName() "%s"', $key, $newKey));
        }
        $element->setParent($this);
        $children = $this->getChildren();
        $children[$newKey] = $element;
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

<?php

/**
 * forked from doctrine/collections
 */

namespace JBJ\Workflow\Collection;

use Doctrine\Common\Collections\ArrayCollection;

/**
 * CollectionTrait
 *
 * Implements Add, Set, Get, and Remove from ArrayCollection. Other methods are
 * implemented in CollectionCommonTrait.
 *
 * Classes using this trait have all of the Doctrine ArrayCollection inhterfaces
 * implemented.
 *      - Countable
 *      - IteratorAggregate
 *      - ArrayAccess
 *      - Selectable

 * Internally, the methods in this trait compose a children collection, which is
 * an ArrayCollection and proxy all calls to it.
 */
trait CollectionTrait
{
    use CollectionCommonTrait;

    public function __clone()
    {
        if (method_exists($this, 'setParent')) {
            $this->setParent(null);
        }
        $children = $this->getChildren();
        $children = new ArrayCollection($children->toArray());
    }

    /**
     * {@inheritdoc}
     */
    public function set($key, $value)
    {
        $this->getChildren()->set($key, $value);
    }

    /**
     * {@inheritdoc}
     */
    public function add($element)
    {
        return $this->getChildren()->add($element);
    }

    /**
     * {@inheritdoc}
     */
    public function remove($key)
    {
        return $this->getChildren()->remove($key);
    }

    /**
     * {@inheritdoc}
     */
    public function removeElement($element)
    {
        return $this->getChildren()->removeElement($element);
    }
}

<?php

namespace JBJ\Workflow\Collection;

/**
 * forked from doctrine/collections
 */

use Closure;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Criteria;

/**
 * CollectionCommonTrait
 *
 * Implements common methods from Doctrine's ArrayCollection class, used by
 * CollectionTrait and GraphCollectionTrait.
 */
trait CollectionCommonTrait
{
    /**
     * Composed collection
     *
     * @var ArrayCollection $children
     */
    private $children;

    /**
     * Save the children :)
     *
     * Save the $elements array until the final ArrayCollection is created.
     *
     * @param array $children
     */
    protected function saveChildren(array $children)
    {
        $this->children = $children;
    }

    /**
     * Get the children
     *
     * The ArrayCollection is created if needed.
     *
     * @return ArrayCollection
     */
    protected function getChildren()
    {
        $children = $this->children;
        if (!$children instanceof ArrayCollection) {
            $children = is_array($children) ? $children : [];
            $this->setChildren($children);
            $children = $this->children;
        }
        return $children;
    }

    /**
     * {@inheritdoc}
     */
    public function first()
    {
        return $this->getChildren()->first();
    }

    /**
     * {@inheritdoc}
     */
    public function last()
    {
        return $this->getChildren()->last();
    }

    /**
     * {@inheritdoc}
     */
    public function key()
    {
        return $this->getChildren()->key();
    }

    /**
     * {@inheritdoc}
     */
    public function next()
    {
        return $this->getChildren()->next();
    }

    /**
     * {@inheritdoc}
     */
    public function current()
    {
        return $this->getChildren()->current();
    }

    /**
     * Required by interface ArrayAccess.
     *
     * {@inheritdoc}
     */
    public function offsetExists($offset)
    {
        return $this->containsKey($offset);
    }

    /**
     * Required by interface ArrayAccess.
     *
     * {@inheritdoc}
     */
    public function offsetGet($offset)
    {
        return $this->get($offset);
    }

    /**
     * Required by interface ArrayAccess.
     *
     * {@inheritdoc}
     */
    public function offsetSet($offset, $value)
    {
        if (!isset($offset)) {
            $this->add($value);
            return;
        }

        $this->set($offset, $value);
    }

    /**
     * Required by interface ArrayAccess.
     *
     * {@inheritdoc}
     */
    public function offsetUnset($offset)
    {
        $this->remove($offset);
    }

    /**
     * {@inheritdoc}
     */
    public function containsKey($key)
    {
        return $this->getChildren()->containsKey($key);
    }

    /**
     * {@inheritdoc}
     */
    public function contains($element)
    {
        return $this->getChildren()->contains($element);
    }

    /**
     * {@inheritdoc}
     */
    public function indexOf($element)
    {
        return $this->getChildren()->indexOf($element);
    }

    /**
     * {@inheritdoc}
     */
    public function get($key)
    {
        return $this->getChildren()->get($key);
    }

    /**
     * {@inheritdoc}
     */
    public function getKeys()
    {
        return $this->getChildren()->getKeys();
    }

    /**
     * {@inheritdoc}
     */
    public function getValues()
    {
        return $this->getChildren()->getValues();
    }

    /**
     * {@inheritdoc}
     */
    public function count()
    {
        return $this->getChildren()->count();
    }

    /**
     * {@inheritdoc}
     */
    public function isEmpty()
    {
        return $this->getChildren()->isEmpty();
    }

    /**
     * Required by interface IteratorAggregate.
     *
     * {@inheritdoc}
     */
    public function getIterator()
    {
        return $this->getChildren()->getIterator();
    }

    /**
     * {@inheritdoc}
     */
    public function exists(Closure $predicate)
    {
        return $this->getChildren()->exists($predicate);
    }

    /**
     * {@inheritdoc}
     *
     * @return static
     */
    public function map(Closure $func)
    {
        return $this->getChildren()->map($func);
    }

    /**
     * {@inheritdoc}
     *
     * @return static
     */
    public function filter(Closure $predicate)
    {
        return $this->getChildren()->filter($predicate);
    }

    /**
     * {@inheritdoc}
     */
    public function forAll(Closure $predicate)
    {
        return $this->getChildren()->forAll($predicate);
    }

    /**
     * {@inheritdoc}
     */
    public function partition(Closure $predicate)
    {
        return $this->getChildren()->partition($predicate);
    }

    /**
     * {@inheritdoc}
     */
    public function slice($offset, $length = null)
    {
        return $this->getChildren()->slice($offset, $length);
    }

    /**
     * {@inheritdoc}
     */
    public function toArray()
    {
        return $this->getChildren()->toArray();
    }

    /**
     * {@inheritdoc}
     */
    public function matching(Criteria $criteria)
    {
        return $this->getChildren()->matching($criteria);
    }

    /**
     * {@inheritdoc}
     */
    public function clear()
    {
        $this->getChildren()->clear();
    }

    /**
     * Returns a string representation of this object.
     *
     * @return string
     */
    public function __toString()
    {
        if (method_exists($this, 'getName')) {
            return $this->getName();
        }
        return strval($this->getChildren());
    }
}

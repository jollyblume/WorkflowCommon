<?php

/**
 * forked from doctrine/collections
 */

namespace JBJ\Workflow\Collection;

use Doctrine\Common\Collections\ArrayCollection;

trait CollectionTrait
{
    use CollectionCommonTrait;

    protected function setChildren(array $elements)
    {
        $children = new ArrayCollection($elements);
        $this->children = $children;
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

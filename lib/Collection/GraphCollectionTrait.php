<?php

namespace JBJ\Workflow\Collection;

/**
 * forked from doctrine/collections
 */

use Doctrine\Common\Collections\ArrayCollection;
use JBJ\Workflow\Collection\ClassnameMetadata;
use JBJ\Workflow\Traits\ElementNameTrait;
use JBJ\Workflow\Traits\ElementParentTrait;
use JBJ\Workflow\Traits\PropertyAccessorTrait;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;

trait GraphCollectionTrait
{
    use CollectionCommonTrait, ElementNameTrait, ElementParentTrait, PropertyAccessorTrait;

    private $constraints;

    protected function initializeTrait(string $name, array $elements = [], array $rules = [], PropertyAccessorInterface $propertyAccessor = null)
    {
        $this->setName($name);
        $constraints = new ArrayCollection();
        foreach (['name', 'parent'] as $key) {
            if (array_key_exists($key, $rules)) {
                $constraints[$key] = new ClassnameMetadata($key, $rules[$key]);
                unset($rules[$key]);
            }
        }
        if (!empty($rules)) {
            throw new \JBJ\Workflow\Exception\FixMeException(sprintf('Unknown integrity rules found "%s"', join(',', array_keys($rules))));
        }
        $ruleErrors = [];
        foreach (['name', 'parent'] as $key) {
            if (!isset($constraints[$key])) {
                $ruleErrors[] = $key;
            }
        }
        if (!empty($ruleErrors)) {
            throw new \JBJ\Workflow\Exception\FixMeException(sprintf('Missing constraints "%s".', join(',', $ruleErrors)));
        }
        $this->constraints = $constraints;
        $this->setPropertyAccessor($propertyAccessor);
        $this->saveChildren($elements);
    }

    private function assertInternalConfigurationIsSet()
    {
        foreach (['name', 'constraints'] as $property) {
            if (null === $this->$property) {
                throw new \JBJ\Workflow\Exception\FixMeException('Internal configuration not set');
            }
        }
    }

    private function setChildren(array $elements)
    {
        $this->assertInternalConfigurationIsSet();
        $this->children = new ArrayCollection();
        foreach ($elements as $key => $element) {
            $this->set($key, $element);
        }
    }

    private function transformKey($element)
    {
        $constraint = $this->constraints['name'];
        $property = $constraint[$element];
        if (null === $property) {
            throw new \JBJ\Workflow\Exception\FixMeException(sprintf('Invalid element "%s", no name property defined.', get_class($element)));
        }
        $propertyAccessor = $this->findPropertyAccessor();
        if (!$propertyAccessor->isReadable($element, $property)) {
            throw new \JBJ\Workflow\Exception\FixMeException(sprintf('Invalid element "%s", property "%s" not readable.', get_class($element), $property));
        }
        $key = $propertyAccessor->getValue($element, $property);
        return $key;
    }

    private function setNewParent($element, $newParent)
    {
        $constraint = $this->constraints['parent'];
        $property = $constraint[$element];
        if (null === $property) {
            throw new \JBJ\Workflow\Exception\FixMeException(sprintf('Invalid element "%s", no parent property defined.', get_class($element)));
        }
        $propertyAccessor = $this->findPropertyAccessor();
        if (!$propertyAccessor->isReadable($element, $property)) {
            throw new \JBJ\Workflow\Exception\FixMeException(sprintf('Invalid element "%s", property "%s" not readable.', get_class($element), $property));
        }
        $propertyAccessor->setValue($element, $property, $newParent);
    }

    public function set($key, $element)
    {
        $key = $this->transformKey($element);
        $this->setNewParent($element, $this);
        $children = $this->getChildren();
        $children[$key] = $element;
    }

    public function add($element)
    {
        $key = $this->transformKey($element);
        if (is_string($key)) {
            $this->set($key, $element);
            return;
        }
        $children = $this->getChildren();
        $children[] = $element;
        $this->setNewParent($element, $this);
    }

    public function remove($key)
    {
        $children = $this->getChildren();
        $element = $children->remove($key);
        if ($element) {
            $this->setNewParent($element, null);
        }
        return $element;
    }

    public function removeElement($element)
    {
        $children = $this->getChildren();
        $success = $children->removeElement($element);
        if ($success) {
            $this->setNewParent($element, null);
        }
        return $success;
    }
}

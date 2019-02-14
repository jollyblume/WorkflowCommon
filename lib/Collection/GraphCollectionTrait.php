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
    private $unusedRules;

    protected function getConstraints()
    {
        $constraints = $this->constraints;
        if (null === $constraints) {
            $constraints = new ArrayCollection();
            $this->constraints = $constraints;
        }
        return $constraints;
    }

    public function getUnusedRules()
    {
        $unusedRules = $this->unusedRules;
        if (null === $unusedRules) {
            $unusedRules = new ArrayCollection();
            $this->unusedRules = $unusedRules;
        }
        return $unusedRules;
    }

    protected function getRequiredRules()
    {
        $requiredRules = [
            'name',
            'parent',
        ];
        return $requiredRules;
    }

    protected function partitionRules(array &$rules, array $targetRules)
    {
        $constraints = $this->getConstraints();
        foreach ($targetRules as $key) {
            if (array_key_exists($key, $rules)) {
                $constraints[$key] = new ClassnameMetadata($key, $rules[$key]);
                unset($rules[$key]);
            }
        }
    }

    protected function assertRequiredRules()
    {
        $requiredRules = $this->getRequiredRules();
        $constraints = $this->getConstraints();
        $ruleErrors = [];
        foreach ($requiredRules as $key) {
            if (!isset($constraints[$key])) {
                $ruleErrors[] = $key;
            }
        }
        if (!empty($ruleErrors)) {
            throw new \JBJ\Workflow\Exception\FixMeException(sprintf('Missing constraints "%s".', join(',', $ruleErrors)));
        }
    }

    protected function initializeTrait(string $name, array $elements = [], array $rules = [], PropertyAccessorInterface $propertyAccessor = null)
    {
        $this->setName($name);
        $this->partitionRules($rules, $this->getRequiredRules());
        $this->assertRequiredRules();
        if (!empty($rules)) {
            $this->unusedRules = new ArrayCollection($rules);
        }
        $this->setPropertyAccessor($propertyAccessor);
        $this->saveChildren($elements);
    }

    private function assertInternalConfigurationIsSet()
    {
        foreach (['name', 'constraints', 'unusedRules'] as $property) {
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
        $constraint = $this->getConstraints()['name'];
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
        $constraint = $this->getConstraints()['parent'];
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

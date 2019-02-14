<?php

namespace JBJ\Workflow\Collection;

use JBJ\Workflow\Collection\ArrayCollectionInterface;
use JBJ\Workflow\Collection\CollectionTrait;
use JBJ\Workflow\Traits\ElementNameTrait;

/**
 * ClassnameMetadata
 *
 * ClassnameMetadata maps classnames to a property name defined in the class
 * which will be referenced in code as ClassnameMetadata::propertyName.
 *
 * It is used by GraphCollection to proxy 'name' and 'parent' properties to
 * semantic properties defined by a class.
 *
 * A default property name can be defined, which is used when rules for a
 * specific classname are not defined.
 *
 * ClassnameMetadata defines two flags to control delivered mappings:
 *  - ClassnameMetadata::isDisabled
 *      If true, all getters return null.
 *  - ClassnameMetadata::isValid
 *      If false, all getters return null.
 *
 * These flags can be set directly or via rules provided to the constructor.
 *
 * Getters will return null if the classname is not defined.
 *
 * ClassnameMetadata implements ArrayCollectionInterface. All accessors use
 * array access semantics.
 */
class ClassnameMetadata implements ArrayCollectionInterface
{
    use CollectionTrait, ElementNameTrait;

    /**
     * Default property name
     *
     * @var string|null $default
     */
    private $default;

    /**
     * Disabled flag
     *
     * If true (default), all getters return null.
     *
     * @var bool $default
     */
    private $isDisabled = true;

    /**
     * Valid flag
     *
     * If false (default), all getters return null.
     *
     * @var string|null $default
     */
    private $isValid = false;

    /**
     * Constructor
     *
     * @param string $propertyName The virtual property name.
     * @param array $rules
     */
    public function __construct(string $propertyName, array $rules = [])
    {
        $this->setName($propertyName);
        if (!empty($rules)) {
            $elements = [];
            foreach ($rules as $key => $value) {
                if ('isDisabled' === $key) {
                    $this->setIsDisabled(boolval($value));
                    continue;
                }
                if ('isValid' === $key) {
                    $this->setIsValid(boolval($value));
                    continue;
                }
                if ('default' === $key || !is_string($key)) {
                    $this->default = $value;
                    continue;
                }
                if (!is_string($value)) {
                    throw new \JBJ\Workflow\Exception\FixMe('Invalid key: not a class');
                }
                if (!class_exists($key)) {
                    throw new \JBJ\Workflow\Exception\FixMe('Invalid key:class not found');
                }
                $elements[$key] = $value;
            }
            $this->setChildren($elements);
        }
    }

    /**
     * Get the proxy property name this rule set describes.
     *
     * @return string
     */
    public function getPropertyName()
    {
        return $this->getName();
    }

    /**
     * Get the isValid flag.
     *
     * @return bool
     */
    public function isValid()
    {
        $isValid = $this->isValid;
        return $isValid;
    }

    /**
     * Set the isValid flag
     *
     * @param bool $valid
     * @return self
     */
    public function setIsValid(bool $valid)
    {
        $this->isValid = $valid;
    }

    /**
    * Get the isDisabled flag
    *
    * @return bool
    */
    public function isDisabled()
    {
        $isDisabled = $this->isDisabled;
        return $isDisabled;
    }

    /**
    * Set the isDisabled flag
    *
    * @param bool $disabled
    * @return self
     */
    public function setIsDisabled(bool $disabled)
    {
        $this->isDisabled = $disabled;
    }

    /**
     * Get the default mapped property name
     *
     * @return string|null
     */
    public function getDefaultValue()
    {
        $default = $this->default;
        return $default;
    }

    /**
     * Has a default mapped property name
     *
     * @return bool
     */
    public function hasDefaultValue()
    {
        $default = $this->getDefaultValue();
        $hasDefault = boolval($default);
        return $hasDefault;
    }

    /**
     * Get a mapped property name
     *
     * Returns the default mapped property name if $classname not in ruleset.
     *
     * @param string $classname
     * @return string Mapped property name for $classname (or default)
     */
    public function get($classname)
    {
        if ($this->isDisabled() || !$this->isValid()) {
            return null;
        }
        if (is_object($classname)) {
            $classname = get_class($classname);
        }
        $value = $this->getChildren()[$classname] ?: $this->getDefaultValue();
        return $value;
    }
}

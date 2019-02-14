<?php

namespace JBJ\Workflow\Tests\Collection;

use PHPUnit\Framework\TestCase;
use JBJ\Workflow\Collection\ClassnameMetadata;

/**
 * @SuppressWarnings(PHPMD.TooManyPublicMethods)
 */
class ClassnameMetadataTest extends TestCase
{
    public function testGetPropertyName()
    {
        $constraint = new ClassnameMetadata('test.key');
        $this->assertEquals('test.key', $constraint->getPropertyName());
    }

    public function testGetReturnsNullByDefault()
    {
        $constraint = new ClassnameMetadata('test.key');
        $this->assertNull($constraint[$this]);
    }

    public function testGetReturnsNullIfDisabled()
    {
        $rules = [
            'isValid' => true,
            'isDisabled' => true,
            get_class() => 'testThis',
            'testDefault', // default property name
        ];
        $constraint = new ClassnameMetadata('test.key', $rules);
        $this->assertNull($constraint[$this]);
    }

    public function testGetReturnsNullIfNotValid()
    {
        $rules = [
            'isValid' => false,
            'isDisabled' => false,
            get_class() => 'testThis',
            'testDefault', // default property name
        ];
        $constraint = new ClassnameMetadata('test.key', $rules);
        $this->assertNull($constraint[$this]);
    }

    public function testGetReturnsDefaultIfSet()
    {
        $rules = [
            'isValid' => true,
            'isDisabled' => false,
            'testDefault', // default property name
        ];
        $constraint = new ClassnameMetadata('test.key', $rules);
        $this->assertEquals('testDefault', $constraint[$this]);
    }

    public function testGetReturnsMappedPropertyNameIfSet()
    {
        $rules = [
            'isValid' => true,
            'isDisabled' => false,
            get_class() => 'testThis',
            'testDefault', // default property name
        ];
        $constraint = new ClassnameMetadata('test.key', $rules);
        $this->assertEquals('testThis', $constraint[$this]);
    }

    public function testSetIsValid()
    {
        $constraint = new ClassnameMetadata('test.key');
        $constraint->setIsValid(true);
        $this->assertTrue($constraint->isValid());
        $constraint->setIsValid(false);
        $this->assertFalse($constraint->isValid());
    }

    public function testSetIsDisabled()
    {
        $constraint = new ClassnameMetadata('test.key');
        $constraint->setIsDisabled(true);
        $this->assertTrue($constraint->isDisabled());
        $constraint->setIsDisabled(false);
        $this->assertFalse($constraint->isDisabled());
    }

    public function testHasDefaultValueFalseByDefault()
    {
        $constraint = new ClassnameMetadata('test.key');
        $this->assertFalse($constraint->hasDefaultValue());
    }

    public function testHasDefaultValueTrueIfSet()
    {
        $constraint = new ClassnameMetadata('test.key', ['testValue']);
        $this->assertTrue($constraint->hasDefaultValue());
    }

    public function testGetDefaultValueNullIfNotSet()
    {
        $constraint = new ClassnameMetadata('test.key');
        $this->assertNull($constraint->getDefaultValue());
    }

    public function testGetDefaultValueIfSet()
    {
        $constraint = new ClassnameMetadata('test.key', ['testValue']);
        $this->assertEquals('testValue', $constraint->getDefaultValue());
    }

    public function testIsDisabledTrueByDefault()
    {
        $constraint = new ClassnameMetadata('test.key');
        $this->assertTrue($constraint->isDisabled());
    }

    public function testIsValidFalseByDefault()
    {
        $constraint = new ClassnameMetadata('test.key');
        $this->assertFalse($constraint->isValid());
    }
}

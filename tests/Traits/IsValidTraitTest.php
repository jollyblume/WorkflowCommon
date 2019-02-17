<?php

namespace JBJ\Workflow\Tests\Traits;

use JBJ\Workflow\Collection\ArrayCollectionInterface;
use JBJ\Workflow\Collection\GraphCollectionTrait;
use JBJ\Workflow\Traits\IsValidTrait;
use JBJ\Workflow\Traits\ElementNameTrait;
use JBJ\Workflow\Traits\ElementParentTrait;
use PHPUnit\Framework\TestCase;

class IsValidTraitTest extends TestCase
{
    protected function getTestClassWithFailOnEmpty(string $name = 'fail-on-empty')
    {
        $testClass = new class($name) implements ArrayCollectionInterface {
            use GraphCollectionTrait, IsValidTrait;

            public function __construct($name)
            {
                $this->setName($name);
            }

            public function isCollectionValid()
            {
                return !$this->isEmpty();
            }
        };
        return $testClass;
    }

    protected function getTestClassWithFailOnMissingChildren(string $name = 'fail-on-missing-children')
    {
        $testClass = new class($name) implements ArrayCollectionInterface {
            use GraphCollectionTrait, IsValidTrait;

            public function __construct($name)
            {
                $this->setName($name);
            }

            public function isCollectionValid()
            {
                foreach (['froms', 'tos'] as $requiredChild) {
                    if (!$this->containsKey($requiredChild)) {
                        return false;
                    }
                }
                return true;
            }
        };
        return $testClass;
    }

    /** @SuppressWarnings(PHPMD) */
    protected function getTestClassFailOnSetter(string $name = 'fail-setter', bool $isValid = false)
    {
        $testClass = new class($name, $isValid) implements ArrayCollectionInterface {
            use GraphCollectionTrait, IsValidTrait;

            public function __construct($name, bool $isValid = false)
            {
                $this->setName($name);
                $this->setIsValid($isValid);
            }

            private $isValid = false;
            public function setIsValid(bool $isValid)
            {
                $this->isValid = $isValid;
            }
            public function isCollectionValid()
            {
                return $this->isValid;
            }
        };
        return $testClass;
    }

    protected function getTestClass()
    {
        $testClass = new class() implements ArrayCollectionInterface {
            use GraphCollectionTrait, IsValidTrait;

            public function __construct()
            {
                $this->setName('base-class');
            }
        };
        return $testClass;
    }

    public function testDefault()
    {
        $testClass = $this->getTestClass();
        $this->assertTrue($testClass->isValid());
        $testClass[] = $this->getTestClassFailOnSetter();
        $this->assertFalse($testClass->isValid());
        $testClass['fail-setter']->setIsValid(true);
        $this->assertTrue($testClass->isValid());
        $this->testClass['no-isValid-method'] = new class('mock') {
            use ElementNameTrait, ElementParentTrait;

            public function __construct(string $name)
            {
                $this->setName($name);
            }
        };
        $this->assertTrue($testClass->isValid());
    }

    public function testEmptyChild()
    {
        $testClass = $this->getTestClass();
        $testClass[] = $this->getTestClassWithFailOnEmpty();
        $this->assertTrue($testClass['fail-on-empty']->isEmpty());
        $this->assertFalse($testClass['fail-on-empty']->isCollectionValid());
        $this->assertFalse($testClass->isValid());
        $testClass['fail-on-empty'][] = $this->getTestClassFailOnSetter('fail-setter', true);
        $this->assertTrue($testClass['fail-on-empty']['fail-setter']->isCollectionValid());
        $this->assertTrue($testClass->isValid());
    }

    public function testRequiredChildren()
    {
        $testClass = $this->getTestClass();
        $testClass[] = $this->getTestClassWithFailOnMissingChildren();
        $this->assertTrue($testClass['fail-on-missing-children']->isEmpty());
        $this->assertFalse($testClass['fail-on-missing-children']->isCollectionValid());
        $this->assertFalse($testClass->isValid());
        $testClass['fail-on-missing-children'][] = $this->getTestClassFailOnSetter('froms', true);
        $this->assertFalse($testClass->isValid());
        $testClass['fail-on-missing-children'][] = $this->getTestClassFailOnSetter('tos', true);
        $this->assertTrue($testClass->isValid());
    }
}

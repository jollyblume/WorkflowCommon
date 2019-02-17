<?php

namespace JBJ\Workflow\Tests\Traits;

use JBJ\Workflow\Collection\ArrayCollectionInterface;
use JBJ\Workflow\Collection\GraphCollectionTrait;
use JBJ\Workflow\Traits\IsValidTrait;
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

            protected function isCollectionValid()
            {
                return !$this->empty;
            }
        };
        return $testClass;
    }

    protected function getTestClassWithFailOnEmptyFailOnMissingChildren(string $name = 'fail-on-missing-children')
    {
        $testClass = new class($name) implements ArrayCollectionInterface {
            use GraphCollectionTrait, IsValidTrait;

            public function __construct($name)
            {
                $this->setName($name);
            }

            protected function isCollectionValid()
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

    protected function getTestClassFailOnSetter(string $name = 'fail-setter')
    {
        $testClass = new class($name) implements ArrayCollectionInterface {
            use GraphCollectionTrait, IsValidTrait;

            public function __construct($name)
            {
                $this->setName($name);
            }

            private $isValid = false;
            public function setIsValid(bool $isValid)
            {
                $this->isValid = $isValid;
            }
            protected function isCollectionValid()
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
    }
}

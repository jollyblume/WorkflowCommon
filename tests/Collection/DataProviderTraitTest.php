<?php

namespace JBJ\Workflow\Tests\Collection;

use PHPUnit\Framework\TestCase;

class DataProviderTraitTest extends TestCase
{
    protected function getTraitFixture()
    {
        $trait = new class() {
            use DataProviderTrait {
                getTestClassname as public;
                setTestClassname as public;
                getTraitNames as public;
                getRelevantTraitNames as public;
            }
        };
        return $trait;
    }

    public function testGetTestClassname()
    {
        $trait = $this->getTraitFixture();
        $this->assertNull($trait->getTestClassname());
    }

    public function testSetTestClassnameWithString()
    {
        $trait = $this->getTraitFixture();
        $trait->setTestClassname(get_class());
        $this->assertEquals(get_class(), $trait->getTestClassname());
    }

    public function testSetTestClassnameWithObject()
    {
        $trait = $this->getTraitFixture();
        $trait->setTestClassname($this);
        $this->assertEquals(get_class(), $trait->getTestClassname());
    }

    /** @expectedException \Exception */
    public function testSetTestClassnameThrowInvalidClass()
    {
        $trait = $this->getTraitFixture();
        $trait->setTestClassname('not-a-class');
    }
}

<?php

namespace JBJ\Workflow\Tests\Collection\Reference;

use JBJ\Workflow\Tests\Collection\BaseCollectionTest;
use JBJ\Workflow\Collection\Reference\UuidLeafCollection;
use JBJ\Workflow\Validator\UuidValidator;

class UuidLeafCollectionTest extends BaseCollectionTest
{
    public function setUp()
    {
        $this->setTestClassname(NodeCollection::class);
    }
}

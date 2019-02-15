<?php

namespace JBJ\Workflow\Traits;

trait ElementNameTrait
{
    private $name;

    protected function setName(string $name)
    {
        $this->name = $name;
    }

    public function getName()
    {
        return $this->name;
    }
}

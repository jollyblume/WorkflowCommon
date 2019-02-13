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
        //todo exception if $name === null (not initialized) ???
        return $this->name;
    }
}

<?php

namespace JBJ\Workflow\Traits;

use JBJ\Workflow\Visitor\NodeVisitorInterface;

trait VisiteeTrait
{
    public function accept(NodeVisitorInterface $visitor): bool
    {
        return $visitor->visit($this);
    }
}

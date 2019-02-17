<?php

namespace JBJ\Workflow\Traits;

trait IsValidTrait
{
    /** @SuppressWarnings(PHPMD.UnusedLocalVariable) */
    protected function isGraphValid()
    {
        $isValid = function ($key, $element) {
            return $element->isValid();
        };
        return $this->forAll($isValid);
    }

    protected function isCollectionValid()
    {
        return true;
    }

    public function isValid()
    {
        return $this->isCollectionValid() && $this->isGraphValid();
    }
}

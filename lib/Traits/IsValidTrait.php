<?php

namespace JBJ\Workflow\Traits;

trait IsValidTrait
{
    /** @SuppressWarnings(PHPMD.UnusedLocalVariable) */
    protected function isGraphValid()
    {
        $isValid = function ($key, $element) {
            if (is_object($element) && method_exists($element, 'isValid')) {
                return $element->isValid();
            }
            return true;
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

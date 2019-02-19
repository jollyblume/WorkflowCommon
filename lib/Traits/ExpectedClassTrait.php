<?php

namespace JBJ\Workflow\Traits;

trait ExpectedClassTrait
{
    private $expectedClasses = [];

    protected function getExpectedClasses()
    {
        return $this->expectedClasses;
    }

    protected function setExpectedClasses(array $expectedClasses)
    {
        $this->expectedClasses = $expectedClasses;
    }

    public function hasExpectedClasses($object)
    {
        $expectedClasses = $this->getExpectedClasses();
        if (empty($expectedClasses)) {
            //todo exception?
            return true;
        }
        $missedClasses = [];
        foreach ($expectedClasses as $classname) {
            if (!is_subclass_of($object, $classname)) {
                $missedClasses[] = $classname;
            }
        }
        return empty($missedClasses);
    }

    protected function assertExpectedClasses($object)
    {
        $has = $this->hasExpectedClasses($object);
        if (!$has) {
            throw new \JBJ\Workflow\Exception\FixMeException(sprintf('Invalid object "%s"', get_class($object)));
        }
    }
}

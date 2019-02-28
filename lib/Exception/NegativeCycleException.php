<?php

namespace JBJ\Workflow\Exception;

use Fhaculty\Graph\Walk;
use JBJ\Workflow;

class NegativeCycleException extends UnexpectedValueException implements Workflow\Exception
{
    /**
     * instance of the cycle
     *
     * @var Walk
     */
    private $cycle;

    public function __construct($message, $code = null, $previous = null, Walk $cycle)
    {
        parent::__construct($message, $code, $previous);
        $this->cycle = $cycle;
    }

    /**
     *
     * @return Walk
     */
    public function getCycle()
    {
        return $this->cycle;
    }
}

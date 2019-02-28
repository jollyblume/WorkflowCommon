<?php

namespace JBJ\Workflow\Exception;

use JBJ\Workflow;

class BadMethodCallException extends \BadMethodCallException implements Workflow\Exception
{
}

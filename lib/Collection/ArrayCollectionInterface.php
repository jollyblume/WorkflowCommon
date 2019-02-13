<?php

namespace JBJ\Workflow\Collection;

use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\Selectable;

/**
 * ComposedCollectionInterface.
 *
 * ComposedCollectionInterface extends the doctrine/collections interfaces
 * implemented by ArrayCollection.
 */
interface ArrayCollectionInterface extends Collection, Selectable
{
}

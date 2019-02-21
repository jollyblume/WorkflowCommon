<?php

namespace JBJ\Workflow;

use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\Selectable;

/**
 * ArrayCollectionInterface.
 *
 * ArrayCollectionInterface extends the doctrine/collections interfaces
 * implemented by ArrayCollection.
 *
 * Classes using CollectionTrait or NodeCollectionTrait implement this interface.
 */
interface ArrayCollectionInterface extends Collection, Selectable
{
}

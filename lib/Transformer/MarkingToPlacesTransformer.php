<?php

namespace JBJ\Workflow\Transformer;

use Symfony\Component\Workflow\Marking;

/**
 * MarkingToPlacesTransformer
 *
 * Transforms a symfony/workflow::Marking <-> array of places
 */
class MarkingToPlacesTransformer
{
    /**
     * Transforms an object (Marking) to an array of places.
     *
     * @param  Marking|null $marking
     * @return array
     */
    public function transform($marking)
    {
        if (!$marking instanceof Marking) {
            return [];
        }
        $places = array_keys($marking->getPlaces());
        return $places;
    }

    /**
     * Transforms an array of places to an object (Marking).
     *
     * @param  array $places
     * @return Marking|null
     */
    public function reverseTransform($places)
    {
        $transformedPlaces = array_flip($places);
        foreach (array_keys($transformedPlaces) as $value) {
            $transformedPlaces[$value] = 1;
        }
        $marking = new Marking($transformedPlaces);
        return $marking;
    }
}

<?php

namespace JBJ\Workflow\Tests\Transformer;

use Symfony\Component\Workflow\Marking;
use JBJ\Workflow\Transformer\MarkingToPlacesTransformer;
use PHPUnit\Framework\TestCase;

class MarkingToPlacesTransformerTest extends TestCase
{
    public function testTransform()
    {
        $transformer = new MarkingToPlacesTransformer();
        $expectedPlaces = [
            'north-pole' => 1,
            'chimney' => 1,
            'snack-table' => 1,
            'roof' => 1,
            'sky' => 1,
        ];
        $marking = new Marking($expectedPlaces);
        $places = $transformer->transform($marking);
        $this->assertEquals(array_keys($expectedPlaces), $places);
    }

    public function testReverseTransform()
    {
        $transformer = new MarkingToPlacesTransformer();
        $expectedPlaces = [
            'north-pole',
            'chimney',
            'snack-table',
            'roof',
            'sky',
        ];
        $marking = $transformer->reverseTransform($expectedPlaces);
        $this->assertEquals($expectedPlaces, array_keys($marking->getPlaces()));
    }

    public function testTransformWhenNull()
    {
        $transformer = new MarkingToPlacesTransformer();
        $marking = $transformer->transform(null);
        $this->assertEquals([], $marking);
    }
}

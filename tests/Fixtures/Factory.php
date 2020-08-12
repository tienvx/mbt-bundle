<?php

namespace Tienvx\Bundle\MbtBundle\Tests\Fixtures;

use SingleColorPetrinet\Model\Color;
use SingleColorPetrinet\Model\ColorfulFactory;
use SingleColorPetrinet\Model\ColorfulFactoryInterface;
use SingleColorPetrinet\Model\Expression;
use Tienvx\Bundle\MbtBundle\Entity\Petrinet\InputArc;
use Tienvx\Bundle\MbtBundle\Entity\Petrinet\Marking;
use Tienvx\Bundle\MbtBundle\Entity\Petrinet\OutputArc;
use Tienvx\Bundle\MbtBundle\Entity\Petrinet\Petrinet;
use Tienvx\Bundle\MbtBundle\Entity\Petrinet\Place;
use Tienvx\Bundle\MbtBundle\Entity\Petrinet\PlaceMarking;
use Tienvx\Bundle\MbtBundle\Entity\Petrinet\Token;
use Tienvx\Bundle\MbtBundle\Entity\Petrinet\Transition;

class Factory
{
    public static function createColorfulFactory(): ColorfulFactoryInterface
    {
        return new ColorfulFactory(
            Color::class,
            Expression::class,
            Petrinet::class,
            Place::class,
            Transition::class,
            InputArc::class,
            OutputArc::class,
            PlaceMarking::class,
            Token::class,
            Marking::class
        );
    }
}

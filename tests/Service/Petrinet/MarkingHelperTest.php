<?php

namespace Tienvx\Bundle\MbtBundle\Tests\Service\Petrinet;

use Petrinet\Model\Marking;
use Petrinet\Model\PlaceMarking;
use Petrinet\Model\Token;
use PHPUnit\Framework\TestCase;
use SingleColorPetrinet\Builder\SingleColorPetrinetBuilder;
use SingleColorPetrinet\Model\Color;
use SingleColorPetrinet\Model\ColorfulFactory;
use SingleColorPetrinet\Model\Place;
use Tienvx\Bundle\MbtBundle\Service\Petrinet\MarkingHelper;

/**
 * @covers \Tienvx\Bundle\MbtBundle\Service\Petrinet\MarkingHelper
 */
class MarkingHelperTest extends TestCase
{
    public function testGetPlaces(): void
    {
        $factory = new ColorfulFactory();
        $helper = new MarkingHelper($factory);
        $pm1 = new PlaceMarking();
        $pm2 = new PlaceMarking();
        $pm1->setPlace($p1 = new Place());
        $p1->setId(1);
        $pm1->setTokens([new Token()]);
        $pm2->setPlace($p2 = new Place());
        $p2->setId(2);
        $pm2->setTokens([new Token(), new Token(), new Token()]);
        $marking = new Marking();
        $marking->setPlaceMarkings([
            $pm1,
            $pm2,
        ]);
        $this->assertSame([
            1 => 1,
            2 => 3,
        ], $helper->getPlaces($marking));
    }

    public function testGetMarking(): void
    {
        $factory = new ColorfulFactory();
        $helper = new MarkingHelper($factory);
        $builder = new SingleColorPetrinetBuilder($factory);

        $petrinet = $builder
            ->connect($place1 = $builder->place(), $transition1 = $builder->transition())
            ->connect($transition1, $place2 = $builder->place())
            ->connect($place3 = $builder->place(), $transition2 = $builder->transition())
            ->connect($transition2, $place4 = $builder->place())
            ->getPetrinet();

        $marking = $helper->getMarking($petrinet, [0 => 1, 2 => 3], new Color(['key' => 'value']));
        $this->assertSame(['key' => 'value'], $marking->getColor()->getValues());
        $this->assertCount(1, $marking->getPlaceMarking($place1)->getTokens());
        $this->assertNull($marking->getPlaceMarking($place2));
        $this->assertCount(3, $marking->getPlaceMarking($place3)->getTokens());
        $this->assertNull($marking->getPlaceMarking($place4));
    }
}

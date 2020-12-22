<?php

namespace Tienvx\Bundle\MbtBundle\Service\Petrinet;

use Petrinet\Builder\MarkingBuilder;
use Petrinet\Model\MarkingInterface;
use Petrinet\Model\PetrinetInterface;
use Petrinet\Model\PlaceMarkingInterface;
use SingleColorPetrinet\Model\ColorfulFactoryInterface;
use SingleColorPetrinet\Model\ColorfulMarkingInterface;
use SingleColorPetrinet\Model\ColorInterface;
use Tienvx\Bundle\MbtBundle\Exception\UnexpectedValueException;

class MarkingHelper implements MarkingHelperInterface
{
    protected ColorfulFactoryInterface $colorfulFactory;

    public function __construct(ColorfulFactoryInterface $colorfulFactory)
    {
        $this->colorfulFactory = $colorfulFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function getPlaces(MarkingInterface $marking): array
    {
        $places = [];
        foreach ($marking->getPlaceMarkings() as $placeMarking) {
            if ($placeMarking instanceof PlaceMarkingInterface && count($placeMarking->getTokens()) > 0) {
                $places[$placeMarking->getPlace()->getId()] = count($placeMarking->getTokens());
            }
        }

        return $places;
    }

    /**
     * {@inheritdoc}
     */
    public function getMarking(
        PetrinetInterface $petrinet,
        array $places,
        ?ColorInterface $color = null
    ): ColorfulMarkingInterface {
        $markingBuilder = new MarkingBuilder($this->colorfulFactory);
        foreach ($places as $place => $tokens) {
            $markingBuilder->mark($petrinet->getPlaces()[$place], $tokens);
        }

        $marking = $markingBuilder->getMarking();
        if (!$marking instanceof ColorfulMarkingInterface) {
            throw new UnexpectedValueException(sprintf(
                'Marking must be instance of %s',
                ColorfulMarkingInterface::class
            ));
        }
        $marking->setColor($this->colorfulFactory->createColor($color ? $color->getValues() : []));

        return $marking;
    }
}

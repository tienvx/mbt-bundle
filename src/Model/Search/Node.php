<?php

namespace Tienvx\Bundle\MbtBundle\Model\Search;

use JMGQ\AStar\AbstractNode;
use Petrinet\Model\PlaceMarkingInterface;
use Tienvx\Bundle\MbtBundle\Model\Petrinet\MarkingInterface;
use Tienvx\Bundle\MbtBundle\Model\Petrinet\TransitionInterface;

class Node extends AbstractNode
{
    protected MarkingInterface $marking;
    protected ?TransitionInterface $transition = null;

    public function __construct(MarkingInterface $marking, ?TransitionInterface $transition = null)
    {
        $this->marking = $marking;
        $this->transition = $transition;
    }

    public function getID(): string
    {
        $tokensCountByPlace = $this->countTokensByPlace();
        ksort($tokensCountByPlace);

        $color = $this->marking->getColor()->toArray();
        ksort($color);

        return md5(serialize([
            'color' => $color,
            'tokens' => $tokensCountByPlace,
        ]));
    }

    public function getMarking(): MarkingInterface
    {
        return $this->marking;
    }

    public function getTransition(): ?TransitionInterface
    {
        return $this->transition;
    }

    public function countTokensByPlace()
    {
        return array_filter(array_combine(
            array_map(
                fn (PlaceMarkingInterface $placeMarking) => $placeMarking->getPlace()->getId(),
                $this->marking->getPlaceMarkings()->toArray()
            ),
            array_map(
                fn (PlaceMarkingInterface $placeMarking) => count($placeMarking->getTokens()),
                $this->marking->getPlaceMarkings()->toArray()
            ),
        ));
    }
}

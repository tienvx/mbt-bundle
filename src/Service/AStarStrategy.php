<?php

namespace Tienvx\Bundle\MbtBundle\Service;

use SingleColorPetrinet\Service\GuardedTransitionServiceInterface;
use Tienvx\Bundle\MbtBundle\Entity\Bug\Step;
use Tienvx\Bundle\MbtBundle\Model\Bug\StepInterface;
use Tienvx\Bundle\MbtBundle\Model\Petrinet\PetrinetInterface;
use Tienvx\Bundle\MbtBundle\Model\Search\AStar;
use Tienvx\Bundle\MbtBundle\Model\Search\Node;

class AStarStrategy implements ShortestPathStrategyInterface
{
    protected GuardedTransitionServiceInterface $transitionService;
    protected ?AStar $aStar;

    public function __construct(GuardedTransitionServiceInterface $transitionService, ?AStar $aStar = null)
    {
        $this->transitionService = $transitionService;
        $this->aStar = $aStar;
    }

    public function run(PetrinetInterface $petrinet, StepInterface $fromStep, StepInterface $toStep): iterable
    {
        $aStar = $this->aStar ?? new AStar();
        $aStar->setTransitionService($this->transitionService);
        $aStar->setPetrinet($petrinet);
        $start = new Node($fromStep->getMarking(), $fromStep->getTransition());
        $goal = new Node($toStep->getMarking(), $toStep->getTransition());

        foreach ($aStar->run($start, $goal) as $node) {
            if ($node instanceof Node) {
                yield new Step($node->getMarking(), $node->getTransition());
            }
        }
    }
}

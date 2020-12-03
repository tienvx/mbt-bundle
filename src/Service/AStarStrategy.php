<?php

namespace Tienvx\Bundle\MbtBundle\Service;

use Petrinet\Model\PetrinetInterface;
use SingleColorPetrinet\Service\GuardedTransitionServiceInterface;
use Tienvx\Bundle\MbtBundle\ValueObject\Bug\Step;
use Tienvx\Bundle\MbtBundle\Model\Bug\StepInterface;
use Tienvx\Bundle\MbtBundle\Model\Search\AStar;
use Tienvx\Bundle\MbtBundle\Model\Search\Node;
use Tienvx\Bundle\MbtBundle\Service\Petrinet\MarkingHelperInterface;

class AStarStrategy implements ShortestPathStrategyInterface
{
    protected GuardedTransitionServiceInterface $transitionService;
    protected MarkingHelperInterface $markingHelper;
    protected ?AStar $aStar;

    public function __construct(
        GuardedTransitionServiceInterface $transitionService,
        MarkingHelperInterface $markingHelper,
        ?AStar $aStar = null
    ) {
        $this->transitionService = $transitionService;
        $this->markingHelper = $markingHelper;
        $this->aStar = $aStar;
    }

    public function run(PetrinetInterface $petrinet, StepInterface $fromStep, StepInterface $toStep): iterable
    {
        $aStar = $this->aStar ?? new AStar();
        $aStar->setTransitionService($this->transitionService);
        $aStar->setMarkingHelper($this->markingHelper);
        $aStar->setPetrinet($petrinet);
        $start = new Node($fromStep->getPlaces(), $fromStep->getColor(), $fromStep->getTransition());
        $goal = new Node($toStep->getPlaces(), $toStep->getColor(), $toStep->getTransition());

        foreach ($aStar->run($start, $goal) as $node) {
            if ($node instanceof Node) {
                yield new Step($node->getPlaces(), $node->getColor(), $node->getTransition());
            }
        }
    }
}

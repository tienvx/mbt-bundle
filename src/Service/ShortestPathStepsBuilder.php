<?php

namespace Tienvx\Bundle\MbtBundle\Service;

use Generator;
use JMGQ\AStar\AStar;
use Tienvx\Bundle\MbtBundle\Exception\ExceptionInterface;
use Tienvx\Bundle\MbtBundle\Exception\OutOfRangeException;
use Tienvx\Bundle\MbtBundle\Model\Bug\StepInterface;
use Tienvx\Bundle\MbtBundle\Model\BugInterface;
use Tienvx\Bundle\MbtBundle\Service\AStar\PetrinetDomainLogicInterface;
use Tienvx\Bundle\MbtBundle\Service\Petrinet\PetrinetHelperInterface;

class ShortestPathStepsBuilder implements StepsBuilderInterface
{
    protected PetrinetHelperInterface $petrinetHelper;
    protected PetrinetDomainLogicInterface $petrinetDomainLogic;

    public function __construct(
        PetrinetHelperInterface $petrinetHelper,
        PetrinetDomainLogicInterface $petrinetDomainLogic
    ) {
        $this->petrinetHelper = $petrinetHelper;
        $this->petrinetDomainLogic = $petrinetDomainLogic;
    }

    /**
     * @throws ExceptionInterface
     */
    public function create(BugInterface $bug, int $from, int $to): Generator
    {
        foreach ($bug->getSteps() as $index => $step) {
            if ($index < $from) {
                yield $step;
            }
        }

        yield from $this->getSteps($bug, $from, $to);

        foreach ($bug->getSteps() as $index => $step) {
            if ($index > $to) {
                yield $step;
            }
        }
    }

    protected function getSteps(BugInterface $bug, int $from, int $to): iterable
    {
        $fromStep = $bug->getSteps()[$from] ?? null;
        $toStep = $bug->getSteps()[$to] ?? null;

        if (!$fromStep instanceof StepInterface || !$toStep instanceof StepInterface) {
            throw new OutOfRangeException('Can not create new steps using invalid range');
        }

        $this->petrinetDomainLogic->setPetrinet($this->petrinetHelper->build($bug->getTask()->getModelRevision()));

        yield from (new AStar($this->petrinetDomainLogic))->run($fromStep, $toStep);

        $this->petrinetDomainLogic->setPetrinet(null);
    }
}

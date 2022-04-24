<?php

namespace Tienvx\Bundle\MbtBundle\Service\Step\Runner;

use Facebook\WebDriver\Remote\RemoteWebDriver;
use Petrinet\Model\PetrinetInterface;
use SingleColorPetrinet\Service\GuardedTransitionServiceInterface;
use Tienvx\Bundle\MbtBundle\Model\Bug\StepInterface;
use Tienvx\Bundle\MbtBundle\Model\DebugInterface;
use Tienvx\Bundle\MbtBundle\Model\Model\RevisionInterface;
use Tienvx\Bundle\MbtBundle\Service\Petrinet\MarkingHelperInterface;
use Tienvx\Bundle\MbtBundle\Service\Petrinet\PetrinetHelperInterface;
use Tienvx\Bundle\MbtBundle\Service\SelenoidHelperInterface;

class ReduceStepsRunner extends BugStepsRunner
{
    protected ?PetrinetInterface $petrinet = null;
    protected PetrinetHelperInterface $petrinetHelper;
    protected MarkingHelperInterface $markingHelper;
    protected GuardedTransitionServiceInterface $transitionService;

    public function __construct(
        SelenoidHelperInterface $selenoidHelper,
        StepRunnerInterface $stepRunner,
        PetrinetHelperInterface $petrinetHelper,
        MarkingHelperInterface $markingHelper,
        GuardedTransitionServiceInterface $transitionService
    ) {
        parent::__construct($selenoidHelper, $stepRunner);
        $this->petrinetHelper = $petrinetHelper;
        $this->markingHelper = $markingHelper;
        $this->transitionService = $transitionService;
    }

    protected function start(DebugInterface $entity): RemoteWebDriver
    {
        $this->petrinet = $this->petrinetHelper->build($entity->getTask()->getModelRevision());

        return parent::start($entity);
    }

    protected function stop(?RemoteWebDriver $driver): void
    {
        parent::stop($driver);
        $this->petrinet = null;
    }

    protected function runStep(StepInterface $step, RevisionInterface $revision, RemoteWebDriver $driver): bool
    {
        $marking = $this->markingHelper->getMarking($this->petrinet, $step->getPlaces(), $step->getColor());
        $transition = $this->petrinet->getTransitions()[$step->getTransition()];
        if ($this->transitionService->isEnabled($transition, $marking)) {
            return parent::runStep($step, $revision, $driver);
        }

        return false;
    }
}

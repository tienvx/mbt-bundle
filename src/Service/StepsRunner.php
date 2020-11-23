<?php

namespace Tienvx\Bundle\MbtBundle\Service;

use SingleColorPetrinet\Service\GuardedTransitionServiceInterface;
use Throwable;
use Tienvx\Bundle\MbtBundle\Exception\RuntimeException;
use Tienvx\Bundle\MbtBundle\Model\Bug\StepInterface;
use Tienvx\Bundle\MbtBundle\Model\Model\TransitionInterface;
use Tienvx\Bundle\MbtBundle\Model\ModelInterface;
use Tienvx\Bundle\MbtBundle\Service\Petrinet\MarkingHelperInterface;
use Tienvx\Bundle\MbtBundle\Service\Petrinet\PetrinetHelperInterface;

class StepsRunner implements StepsRunnerInterface
{
    protected PetrinetHelperInterface $petrinetHelper;
    protected MarkingHelperInterface $markingHelper;
    protected GuardedTransitionServiceInterface $transitionService;
    protected StepRunnerInterface $stepRunner;

    public function __construct(
        PetrinetHelperInterface $petrinetHelper,
        MarkingHelperInterface $markingHelper,
        GuardedTransitionServiceInterface $transitionService,
        StepRunnerInterface $stepRunner
    ) {
        $this->petrinetHelper = $petrinetHelper;
        $this->markingHelper = $markingHelper;
        $this->transitionService = $transitionService;
        $this->stepRunner = $stepRunner;
    }

    /**
     * @var StepInterface[]
     * @var ModelInterface
     *
     * @throws Throwable
     */
    public function run(iterable $steps, ModelInterface $model): iterable
    {
        $this->stepRunner->setUp();
        $petrinet = $this->petrinetHelper->build($model);
        foreach ($steps as $step) {
            try {
                $transition = $petrinet->getTransitions()[$step->getTransition()];
                $marking = $this->markingHelper->getMarking($petrinet, $step->getPlaces());
                if (
                    $transition instanceof TransitionInterface &&
                    $step instanceof StepInterface &&
                    $this->transitionService->isEnabled($transition, $marking)
                ) {
                    $this->transitionService->fire($transition, $marking);
                    $this->stepRunner->run($step);
                } else {
                    throw new RuntimeException(sprintf('Transition %d is not enabled', $step->getTransition()));
                }
            } catch (Throwable $throwable) {
                $this->stepRunner->tearDown();
                throw $throwable;
            } finally {
                yield $step;
            }
        }
        $this->stepRunner->tearDown();
    }
}

<?php

namespace Tienvx\Bundle\MbtBundle\Service;

use SingleColorPetrinet\Service\GuardedTransitionServiceInterface;
use Throwable;
use Tienvx\Bundle\MbtBundle\Exception\RuntimeException;
use Tienvx\Bundle\MbtBundle\Model\Bug\StepInterface;

class StepsRunner implements StepsRunnerInterface
{
    protected GuardedTransitionServiceInterface $transitionService;
    protected StepRunnerInterface $stepRunner;

    public function __construct(GuardedTransitionServiceInterface $transitionService, StepRunnerInterface $stepRunner)
    {
        $this->transitionService = $transitionService;
        $this->stepRunner = $stepRunner;
    }

    /**
     * @throws Throwable
     */
    public function run(iterable $steps): iterable
    {
        $this->stepRunner->setUp();
        foreach ($steps as $step) {
            try {
                if ($step instanceof StepInterface && $this->transitionService->isEnabled($step->getTransition(), $step->getMarking())) {
                    $this->transitionService->fire($step->getTransition(), $step->getMarking());
                    $this->stepRunner->run($step);
                } else {
                    throw new RuntimeException(sprintf('Transition %d is not enabled', $step->getTransition()->getId()));
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

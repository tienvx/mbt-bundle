<?php

namespace Tienvx\Bundle\MbtBundle\Service;

use Petrinet\Model\TransitionInterface;
use SingleColorPetrinet\Service\GuardedTransitionServiceInterface;
use Throwable;
use Tienvx\Bundle\MbtBundle\Exception\RuntimeException;
use Tienvx\Bundle\MbtBundle\Model\Bug\StepInterface;
use Tienvx\Bundle\MbtBundle\Model\TaskInterface;
use Tienvx\Bundle\MbtBundle\Provider\ProviderManager;
use Tienvx\Bundle\MbtBundle\Service\Petrinet\MarkingHelperInterface;
use Tienvx\Bundle\MbtBundle\Service\Petrinet\PetrinetHelperInterface;

class StepsRunner implements StepsRunnerInterface
{
    protected PetrinetHelperInterface $petrinetHelper;
    protected MarkingHelperInterface $markingHelper;
    protected GuardedTransitionServiceInterface $transitionService;
    protected StepRunnerInterface $stepRunner;
    protected ProviderManager $providerManager;

    public function __construct(
        PetrinetHelperInterface $petrinetHelper,
        MarkingHelperInterface $markingHelper,
        GuardedTransitionServiceInterface $transitionService,
        StepRunnerInterface $stepRunner,
        ProviderManager $providerManager
    ) {
        $this->petrinetHelper = $petrinetHelper;
        $this->markingHelper = $markingHelper;
        $this->transitionService = $transitionService;
        $this->stepRunner = $stepRunner;
        $this->providerManager = $providerManager;
    }

    /**
     * @var StepInterface[]
     * @var TaskInterface
     * @var bool
     *
     * @throws Throwable
     */
    public function run(iterable $steps, TaskInterface $task, ?int $recordVideoBugId = null): iterable
    {
        $driver = $this->providerManager->createDriver($task, $recordVideoBugId);
        $petrinet = $this->petrinetHelper->build($task->getModel());
        foreach ($steps as $step) {
            if (!$step instanceof StepInterface) {
                continue;
            }
            try {
                $transition = $petrinet->getTransitions()[$step->getTransition()];
                $marking = $this->markingHelper->getMarking($petrinet, $step->getPlaces(), $step->getColor());
                if (
                    $transition instanceof TransitionInterface &&
                    $this->transitionService->isEnabled($transition, $marking)
                ) {
                    $this->transitionService->fire($transition, $marking);
                    $this->stepRunner->run($step, $task->getModel(), $driver);
                } else {
                    throw new RuntimeException(sprintf('Transition %d is not enabled', $step->getTransition()));
                }
            } catch (Throwable $throwable) {
                $driver->quit();
                throw $throwable;
            } finally {
                yield $step;
            }
        }
        $driver->quit();
    }
}

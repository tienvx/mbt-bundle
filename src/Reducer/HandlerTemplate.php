<?php

namespace Tienvx\Bundle\MbtBundle\Reducer;

use Symfony\Component\Messenger\MessageBusInterface;
use Throwable;
use Tienvx\Bundle\MbtBundle\Message\ReduceBugMessage;
use Tienvx\Bundle\MbtBundle\Model\BugInterface;
use Tienvx\Bundle\MbtBundle\Repository\BugRepositoryInterface;
use Tienvx\Bundle\MbtBundle\Service\Step\Builder\StepsBuilderInterface;
use Tienvx\Bundle\MbtBundle\Service\Step\Runner\ReduceStepsRunner;
use Tienvx\Bundle\MbtBundle\Service\Step\StepHelperInterface;

abstract class HandlerTemplate implements HandlerInterface
{
    protected BugRepositoryInterface $bugRepository;
    protected MessageBusInterface $messageBus;
    protected ReduceStepsRunner $stepsRunner;
    protected StepsBuilderInterface $stepsBuilder;
    protected StepHelperInterface $stepHelper;

    public function __construct(
        BugRepositoryInterface $bugRepository,
        MessageBusInterface $messageBus,
        ReduceStepsRunner $stepsRunner,
        StepsBuilderInterface $stepsBuilder,
        StepHelperInterface $stepHelper
    ) {
        $this->bugRepository = $bugRepository;
        $this->messageBus = $messageBus;
        $this->stepsRunner = $stepsRunner;
        $this->stepsBuilder = $stepsBuilder;
        $this->stepHelper = $stepHelper;
    }

    public function handle(BugInterface $bug, int $from, int $to): void
    {
        $newSteps = iterator_to_array($this->stepsBuilder->create($bug, $from, $to));
        if (count($newSteps) >= count($bug->getSteps())) {
            return;
        }

        $bug->setDebug(false);
        $this->stepsRunner->run(
            $this->stepHelper->cloneAndResetSteps($newSteps, $bug->getTask()->getModelRevision()),
            $bug,
            function (Throwable $throwable) use ($bug, $newSteps): void {
                if ($throwable->getMessage() === $bug->getMessage()) {
                    $this->bugRepository->updateSteps($bug, $newSteps);
                    $this->messageBus->dispatch(new ReduceBugMessage($bug->getId()));
                }
            }
        );
    }
}

<?php

namespace Tienvx\Bundle\MbtBundle\Reducer;

use Symfony\Component\Messenger\MessageBusInterface;
use Throwable;
use Tienvx\Bundle\MbtBundle\Message\ReduceBugMessage;
use Tienvx\Bundle\MbtBundle\Model\BugInterface;
use Tienvx\Bundle\MbtBundle\Repository\BugRepositoryInterface;
use Tienvx\Bundle\MbtBundle\Service\StepsBuilderInterface;
use Tienvx\Bundle\MbtBundle\Service\StepsRunnerInterface;

abstract class HandlerTemplate implements HandlerInterface
{
    protected BugRepositoryInterface $bugRepository;
    protected MessageBusInterface $messageBus;
    protected StepsRunnerInterface $stepsRunner;
    protected StepsBuilderInterface $stepsBuilder;

    public function __construct(
        BugRepositoryInterface $bugRepository,
        MessageBusInterface $messageBus,
        StepsRunnerInterface $stepsRunner,
        StepsBuilderInterface $stepsBuilder
    ) {
        $this->bugRepository = $bugRepository;
        $this->messageBus = $messageBus;
        $this->stepsRunner = $stepsRunner;
        $this->stepsBuilder = $stepsBuilder;
    }

    public function handle(BugInterface $bug, int $from, int $to): void
    {
        $newSteps = iterator_to_array($this->stepsBuilder->create($bug, $from, $to));
        if (count($newSteps) >= count($bug->getSteps())) {
            return;
        }

        $this->stepsRunner->run($newSteps, $bug, false, function (Throwable $throwable) use ($bug, $newSteps): void {
            if ($throwable->getMessage() === $bug->getMessage()) {
                $this->bugRepository->updateSteps($bug, $newSteps);
                $this->messageBus->dispatch(new ReduceBugMessage($bug->getId()));
            }
        });
    }
}

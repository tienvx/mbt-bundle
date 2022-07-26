<?php

namespace Tienvx\Bundle\MbtBundle\Reducer;

use Symfony\Component\Messenger\MessageBusInterface;
use Throwable;
use Tienvx\Bundle\MbtBundle\Exception\StepsNotConnectedException;
use Tienvx\Bundle\MbtBundle\Message\ReduceBugMessage;
use Tienvx\Bundle\MbtBundle\Model\BugInterface;
use Tienvx\Bundle\MbtBundle\Repository\BugRepositoryInterface;
use Tienvx\Bundle\MbtBundle\Service\ConfigInterface;
use Tienvx\Bundle\MbtBundle\Service\Step\Builder\StepsBuilderInterface;
use Tienvx\Bundle\MbtBundle\Service\Step\Runner\BugStepsRunner;

abstract class HandlerTemplate implements HandlerInterface
{
    public function __construct(
        protected BugRepositoryInterface $bugRepository,
        protected MessageBusInterface $messageBus,
        protected BugStepsRunner $stepsRunner,
        protected StepsBuilderInterface $stepsBuilder,
        protected ConfigInterface $config
    ) {
    }

    public function handle(BugInterface $bug, int $from, int $to): void
    {
        try {
            $newSteps = iterator_to_array($this->stepsBuilder->create($bug, $from, $to));
        } catch (StepsNotConnectedException $exception) {
            return;
        }

        if (count($newSteps) >= count($bug->getSteps())) {
            return;
        }

        $bug->setDebug(false);
        $this->stepsRunner->run(
            $newSteps,
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

<?php

namespace Tienvx\Bundle\MbtBundle\Reducer;

use Doctrine\DBAL\LockMode;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Messenger\MessageBusInterface;
use Throwable;
use Tienvx\Bundle\MbtBundle\Exception\ExceptionInterface;
use Tienvx\Bundle\MbtBundle\Message\ReduceBugMessage;
use Tienvx\Bundle\MbtBundle\Model\BugInterface;
use Tienvx\Bundle\MbtBundle\Service\StepsBuilderInterface;
use Tienvx\Bundle\MbtBundle\Service\StepsRunnerInterface;

abstract class HandlerTemplate implements HandlerInterface
{
    protected EntityManagerInterface $entityManager;
    protected MessageBusInterface $messageBus;
    protected StepsRunnerInterface $stepsRunner;
    protected StepsBuilderInterface $stepsBuilder;

    public function __construct(
        EntityManagerInterface $entityManager,
        MessageBusInterface $messageBus,
        StepsRunnerInterface $stepsRunner,
        StepsBuilderInterface $stepsBuilder
    ) {
        $this->entityManager = $entityManager;
        $this->messageBus = $messageBus;
        $this->stepsRunner = $stepsRunner;
        $this->stepsBuilder = $stepsBuilder;
    }

    /**
     * @throws ExceptionInterface
     */
    public function handle(BugInterface $bug, int $from, int $to): void
    {
        $newSteps = [...$this->stepsBuilder->create($bug, $from, $to)];
        if (count($newSteps) >= $bug->getSteps()->count()) {
            return;
        }

        $this->run($newSteps, $bug);
    }

    /**
     * @throws ExceptionInterface
     */
    protected function run(array $newSteps, BugInterface $bug): void
    {
        try {
            $this->stepsRunner->run($newSteps);
        } catch (ExceptionInterface $exception) {
            throw $exception;
        } catch (Throwable $throwable) {
            if ($throwable->getMessage() === $bug->getMessage()) {
                $this->updateSteps($bug, $newSteps);
                $this->messageBus->dispatch(new ReduceBugMessage($bug->getId()));
            }
        }
    }

    public function updateSteps(BugInterface $bug, array $newSteps): void
    {
        $callback = function () use ($bug, $newSteps): void {
            // Refresh the bug for the latest steps's length.
            $this->entityManager->refresh($bug);

            if (count($newSteps) <= $bug->getSteps()->count()) {
                $this->entityManager->lock($bug, LockMode::PESSIMISTIC_WRITE);
                $bug->setSteps($newSteps);
            }
        };

        $this->entityManager->transactional($callback);
    }
}

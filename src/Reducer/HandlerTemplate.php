<?php

namespace Tienvx\Bundle\MbtBundle\Reducer;

use Doctrine\DBAL\LockMode;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Messenger\MessageBusInterface;
use Throwable;
use Tienvx\Bundle\MbtBundle\Exception\ExceptionInterface;
use Tienvx\Bundle\MbtBundle\Message\ReduceBugMessage;
use Tienvx\Bundle\MbtBundle\Model\BugInterface;
use Tienvx\Bundle\MbtBundle\Service\Bug\BugHelperInterface;
use Tienvx\Bundle\MbtBundle\Service\SelenoidHelperInterface;
use Tienvx\Bundle\MbtBundle\Service\StepRunnerInterface;
use Tienvx\Bundle\MbtBundle\Service\StepsBuilderInterface;

abstract class HandlerTemplate implements HandlerInterface
{
    protected EntityManagerInterface $entityManager;
    protected MessageBusInterface $messageBus;
    protected StepRunnerInterface $stepRunner;
    protected StepsBuilderInterface $stepsBuilder;
    protected BugHelperInterface $bugHelper;
    protected SelenoidHelperInterface $selenoidHelper;

    public function __construct(
        EntityManagerInterface $entityManager,
        MessageBusInterface $messageBus,
        StepRunnerInterface $stepRunner,
        StepsBuilderInterface $stepsBuilder,
        BugHelperInterface $bugHelper,
        SelenoidHelperInterface $selenoidHelper
    ) {
        $this->entityManager = $entityManager;
        $this->messageBus = $messageBus;
        $this->stepRunner = $stepRunner;
        $this->stepsBuilder = $stepsBuilder;
        $this->bugHelper = $bugHelper;
        $this->selenoidHelper = $selenoidHelper;
    }

    /**
     * @throws ExceptionInterface
     */
    public function handle(BugInterface $bug, int $from, int $to): void
    {
        $newSteps = iterator_to_array($this->stepsBuilder->create($bug, $from, $to));
        if (count($newSteps) >= count($bug->getSteps())) {
            return;
        }

        $this->run($newSteps, $bug);
    }

    /**
     * @throws ExceptionInterface
     */
    protected function run(array $newSteps, BugInterface $bug): void
    {
        $driver = $this->selenoidHelper->createDriver($this->selenoidHelper->getCapabilities($bug));
        try {
            foreach ($newSteps as $step) {
                $this->stepRunner->run($step, $bug->getTask()->getModelRevision(), $driver);
            }
        } catch (ExceptionInterface $exception) {
            throw $exception;
        } catch (Throwable $throwable) {
            if ($throwable->getMessage() === $bug->getMessage()) {
                $this->updateSteps($bug, $newSteps);
                $this->messageBus->dispatch(new ReduceBugMessage($bug->getId()));
            }
        } finally {
            $driver->quit();
        }
    }

    public function updateSteps(BugInterface $bug, array $newSteps): void
    {
        $callback = function () use ($bug, $newSteps): void {
            // Refresh the bug for the latest steps's length.
            $this->entityManager->refresh($bug);

            if (count($newSteps) <= count($bug->getSteps())) {
                $this->entityManager->lock($bug, LockMode::PESSIMISTIC_WRITE);
                $bug->setReducing(false);
                $bug->getProgress()->setTotal(0);
                $bug->getProgress()->setProcessed(0);
                $bug->setSteps($newSteps);
            }
        };

        $this->entityManager->transactional($callback);
    }
}

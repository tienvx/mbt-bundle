<?php

namespace Tienvx\Bundle\MbtBundle\Reducer;

use Doctrine\DBAL\LockMode;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Messenger\MessageBusInterface;
use Throwable;
use Tienvx\Bundle\MbtBundle\Exception\ExceptionInterface;
use Tienvx\Bundle\MbtBundle\Message\ReduceBugMessage;
use Tienvx\Bundle\MbtBundle\Model\BugInterface;
use Tienvx\Bundle\MbtBundle\Provider\ProviderManager;
use Tienvx\Bundle\MbtBundle\Service\Bug\BugHelperInterface;
use Tienvx\Bundle\MbtBundle\Service\StepRunnerInterface;
use Tienvx\Bundle\MbtBundle\Service\StepsBuilderInterface;

abstract class HandlerTemplate implements HandlerInterface
{
    protected ProviderManager $providerManager;
    protected EntityManagerInterface $entityManager;
    protected MessageBusInterface $messageBus;
    protected StepRunnerInterface $stepRunner;
    protected StepsBuilderInterface $stepsBuilder;
    protected BugHelperInterface $bugHelper;

    public function __construct(
        ProviderManager $providerManager,
        EntityManagerInterface $entityManager,
        MessageBusInterface $messageBus,
        StepRunnerInterface $stepRunner,
        StepsBuilderInterface $stepsBuilder,
        BugHelperInterface $bugHelper
    ) {
        $this->providerManager = $providerManager;
        $this->entityManager = $entityManager;
        $this->messageBus = $messageBus;
        $this->stepRunner = $stepRunner;
        $this->stepsBuilder = $stepsBuilder;
        $this->bugHelper = $bugHelper;
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
        $driver = $this->providerManager->createDriver($bug->getTask());
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
            } else {
                $bug->getTask()->addBug($this->bugHelper->createBug($newSteps, $throwable->getMessage()));
                $this->entityManager->flush();
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
                $bug->setSteps($newSteps);
            }
        };

        $this->entityManager->transactional($callback);
    }
}

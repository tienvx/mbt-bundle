<?php

namespace Tienvx\Bundle\MbtBundle\MessageHandler;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;
use Throwable;
use Tienvx\Bundle\MbtBundle\Entity\Task;
use Tienvx\Bundle\MbtBundle\Exception\ExceptionInterface;
use Tienvx\Bundle\MbtBundle\Exception\UnexpectedValueException;
use Tienvx\Bundle\MbtBundle\Generator\GeneratorManagerInterface;
use Tienvx\Bundle\MbtBundle\Message\ExecuteTaskMessage;
use Tienvx\Bundle\MbtBundle\Model\Bug\StepInterface;
use Tienvx\Bundle\MbtBundle\Model\TaskInterface;
use Tienvx\Bundle\MbtBundle\Provider\ProviderManagerInterface;
use Tienvx\Bundle\MbtBundle\Service\Bug\BugHelperInterface;
use Tienvx\Bundle\MbtBundle\Service\StepRunnerInterface;

class ExecuteTaskMessageHandler implements MessageHandlerInterface
{
    protected GeneratorManagerInterface $generatorManager;
    protected ProviderManagerInterface $providerManager;
    protected EntityManagerInterface $entityManager;
    protected StepRunnerInterface $stepRunner;
    protected BugHelperInterface $bugHelper;
    protected int $maxSteps;

    public function __construct(
        GeneratorManagerInterface $generatorManager,
        ProviderManagerInterface $providerManager,
        EntityManagerInterface $entityManager,
        StepRunnerInterface $stepRunner,
        BugHelperInterface $bugHelper
    ) {
        $this->generatorManager = $generatorManager;
        $this->providerManager = $providerManager;
        $this->entityManager = $entityManager;
        $this->stepRunner = $stepRunner;
        $this->bugHelper = $bugHelper;
    }

    public function setMaxSteps(int $maxSteps): void
    {
        $this->maxSteps = $maxSteps;
    }

    /**
     * @throws ExceptionInterface
     */
    public function __invoke(ExecuteTaskMessage $message): void
    {
        $taskId = $message->getId();
        $task = $this->entityManager->find(Task::class, $taskId);

        if (!$task instanceof TaskInterface) {
            throw new UnexpectedValueException(sprintf('Can not execute task %d: task not found', $taskId));
        }

        $this->execute($task);
    }

    /**
     * @throws ExceptionInterface
     */
    protected function execute(TaskInterface $task): void
    {
        $steps = [];
        $generator = $this->generatorManager->getGenerator($task->getTaskConfig()->getGenerator());
        $driver = $this->providerManager->createDriver($task);
        $task->getProgress()->setTotal($this->maxSteps);
        try {
            foreach ($generator->generate($task) as $step) {
                if ($step instanceof StepInterface) {
                    $steps[] = clone $step;
                    $task->getProgress()->increase();
                    $this->stepRunner->run($step, $task->getModelRevision(), $driver);
                }
                if (count($steps) >= $this->maxSteps) {
                    break;
                }
            }
        } catch (ExceptionInterface $exception) {
            throw $exception;
        } catch (Throwable $throwable) {
            $task->addBug($this->bugHelper->createBug($steps, $throwable->getMessage(), $task->getId()));
        } finally {
            $driver->quit();
            $task->getProgress()->setTotal(count($steps));
            // Executing task take long time. Reconnect to flush changes.
            $this->entityManager->getConnection()->connect();
            $this->entityManager->flush();
        }
    }
}

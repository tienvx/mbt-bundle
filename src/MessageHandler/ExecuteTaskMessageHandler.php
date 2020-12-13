<?php

namespace Tienvx\Bundle\MbtBundle\MessageHandler;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;
use Throwable;
use Tienvx\Bundle\MbtBundle\Entity\Task;
use Tienvx\Bundle\MbtBundle\Exception\ExceptionInterface;
use Tienvx\Bundle\MbtBundle\Exception\UnexpectedValueException;
use Tienvx\Bundle\MbtBundle\Generator\GeneratorManager;
use Tienvx\Bundle\MbtBundle\Message\ExecuteTaskMessage;
use Tienvx\Bundle\MbtBundle\Model\Bug\StepInterface;
use Tienvx\Bundle\MbtBundle\Model\TaskInterface;
use Tienvx\Bundle\MbtBundle\Service\BugHelperInterface;
use Tienvx\Bundle\MbtBundle\Service\ConfigLoaderInterface;
use Tienvx\Bundle\MbtBundle\Service\StepsRunnerInterface;
use Tienvx\Bundle\MbtBundle\Service\TaskProgressInterface;

class ExecuteTaskMessageHandler implements MessageHandlerInterface
{
    protected GeneratorManager $generatorManager;
    protected EntityManagerInterface $entityManager;
    protected StepsRunnerInterface $stepsRunner;
    protected ConfigLoaderInterface $configLoader;
    protected TaskProgressInterface $taskProgress;
    protected BugHelperInterface $bugHelper;

    public function __construct(
        GeneratorManager $generatorManager,
        EntityManagerInterface $entityManager,
        StepsRunnerInterface $stepsRunner,
        ConfigLoaderInterface $configLoader,
        TaskProgressInterface $taskProgress,
        BugHelperInterface $bugHelper
    ) {
        $this->generatorManager = $generatorManager;
        $this->entityManager = $entityManager;
        $this->stepsRunner = $stepsRunner;
        $this->configLoader = $configLoader;
        $this->taskProgress = $taskProgress;
        $this->bugHelper = $bugHelper;
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
        $generator = $this->generatorManager->get($this->configLoader->getGenerator());
        $this->taskProgress->setTotal($task, $this->configLoader->getMaxSteps());
        try {
            foreach ($this->stepsRunner->run($generator->generate($task->getModel()), $task) as $step) {
                if ($step instanceof StepInterface) {
                    $steps[] = $step;
                    $this->taskProgress->increaseProcessed($task, 1);
                }
            }
        } catch (ExceptionInterface $exception) {
            throw $exception;
        } catch (Throwable $throwable) {
            $bug = $this->bugHelper->create($steps, $throwable->getMessage(), $task);
            $this->entityManager->persist($bug);
        } finally {
            // Executing task take long time. Reconnect to flush changes.
            $this->entityManager->getConnection()->connect();
            $this->entityManager->flush();
        }
    }
}

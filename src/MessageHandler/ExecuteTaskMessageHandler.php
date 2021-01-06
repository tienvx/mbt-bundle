<?php

namespace Tienvx\Bundle\MbtBundle\MessageHandler;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use Throwable;
use Tienvx\Bundle\MbtBundle\Entity\Bug;
use Tienvx\Bundle\MbtBundle\Entity\Task;
use Tienvx\Bundle\MbtBundle\Exception\ExceptionInterface;
use Tienvx\Bundle\MbtBundle\Exception\UnexpectedValueException;
use Tienvx\Bundle\MbtBundle\Generator\GeneratorManagerInterface;
use Tienvx\Bundle\MbtBundle\Message\ExecuteTaskMessage;
use Tienvx\Bundle\MbtBundle\Model\Bug\StepInterface;
use Tienvx\Bundle\MbtBundle\Model\BugInterface;
use Tienvx\Bundle\MbtBundle\Model\TaskInterface;
use Tienvx\Bundle\MbtBundle\Provider\ProviderManagerInterface;
use Tienvx\Bundle\MbtBundle\Service\StepRunnerInterface;
use Tienvx\Bundle\MbtBundle\Service\TaskProgressInterface;

class ExecuteTaskMessageHandler implements MessageHandlerInterface
{
    protected GeneratorManagerInterface $generatorManager;
    protected ProviderManagerInterface $providerManager;
    protected EntityManagerInterface $entityManager;
    protected StepRunnerInterface $stepRunner;
    protected TaskProgressInterface $taskProgress;
    protected TranslatorInterface $translator;
    protected int $maxSteps;

    public function __construct(
        GeneratorManagerInterface $generatorManager,
        ProviderManagerInterface $providerManager,
        EntityManagerInterface $entityManager,
        StepRunnerInterface $stepRunner,
        TaskProgressInterface $taskProgress,
        TranslatorInterface $translator
    ) {
        $this->generatorManager = $generatorManager;
        $this->providerManager = $providerManager;
        $this->entityManager = $entityManager;
        $this->stepRunner = $stepRunner;
        $this->taskProgress = $taskProgress;
        $this->translator = $translator;
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
        $this->taskProgress->setTotal($task, $this->maxSteps);
        try {
            foreach ($generator->generate($task) as $step) {
                if ($step instanceof StepInterface) {
                    $steps[] = $step;
                    $this->taskProgress->increaseProcessed($task, 1);
                    $this->stepRunner->run($step, $task->getModel(), $driver);
                }
                if (count($steps) >= $this->maxSteps) {
                    break;
                }
            }
        } catch (ExceptionInterface $exception) {
            throw $exception;
        } catch (Throwable $throwable) {
            $bug = $this->createBug($steps, $throwable->getMessage(), $task);
            $this->entityManager->persist($bug);
        } finally {
            $driver->quit();
            // Executing task take long time. Reconnect to flush changes.
            $this->entityManager->getConnection()->connect();
            $this->entityManager->flush();
        }
    }

    protected function createBug(array $steps, string $message, TaskInterface $task): BugInterface
    {
        $bug = new Bug();
        $bug->setTitle($this->translator->trans('mbt.default_bug_title', ['%model%' => $task->getModel()->getLabel()]));
        $bug->setSteps($steps);
        $bug->setMessage($message);
        $bug->setTask($task);
        $bug->setModelVersion($task->getModel()->getVersion());

        return $bug;
    }
}

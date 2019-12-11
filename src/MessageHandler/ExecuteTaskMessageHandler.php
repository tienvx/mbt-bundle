<?php

namespace Tienvx\Bundle\MbtBundle\MessageHandler;

use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;
use Symfony\Component\Messenger\MessageBusInterface;
use Throwable;
use Tienvx\Bundle\MbtBundle\Entity\Task;
use Tienvx\Bundle\MbtBundle\Generator\GeneratorManager;
use Tienvx\Bundle\MbtBundle\Helper\MessageHelper;
use Tienvx\Bundle\MbtBundle\Helper\ModelHelper;
use Tienvx\Bundle\MbtBundle\Helper\Steps\Recorder as StepsRecorder;
use Tienvx\Bundle\MbtBundle\Message\ApplyTaskTransitionMessage;
use Tienvx\Bundle\MbtBundle\Message\ExecuteTaskMessage;
use Tienvx\Bundle\MbtBundle\Steps\Steps;
use Tienvx\Bundle\MbtBundle\Subject\SubjectManager;
use Tienvx\Bundle\MbtBundle\Workflow\TaskWorkflow;

class ExecuteTaskMessageHandler implements MessageHandlerInterface
{
    /**
     * @var SubjectManager
     */
    private $subjectManager;

    /**
     * @var GeneratorManager
     */
    private $generatorManager;

    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     * @var MessageHelper
     */
    private $messageHelper;

    /**
     * @var ModelHelper
     */
    private $modelHelper;

    /**
     * @var MessageBusInterface
     */
    private $messageBus;

    /**
     * @var StepsRecorder
     */
    private $stepsRecorder;

    public function __construct(
        SubjectManager $subjectManager,
        GeneratorManager $generatorManager,
        EntityManagerInterface $entityManager,
        MessageHelper $messageHelper,
        ModelHelper $modelHelper,
        MessageBusInterface $messageBus,
        StepsRecorder $stepsRecorder
    ) {
        $this->subjectManager = $subjectManager;
        $this->generatorManager = $generatorManager;
        $this->entityManager = $entityManager;
        $this->messageHelper = $messageHelper;
        $this->modelHelper = $modelHelper;
        $this->messageBus = $messageBus;
        $this->stepsRecorder = $stepsRecorder;
    }

    public function __invoke(ExecuteTaskMessage $message): void
    {
        $taskId = $message->getId();
        $task = $this->entityManager->find(Task::class, $taskId);

        if (!$task instanceof Task) {
            throw new Exception(sprintf('No task found for id %d', $taskId));
        }

        $this->execute($task);
    }

    protected function execute(Task $task): void
    {
        $subject = $this->subjectManager->createAndSetUp($task->getModel()->getName());
        $generator = $this->generatorManager->get($task->getGenerator()->getName());
        $model = $this->modelHelper->get($task->getModel()->getName());

        $recorded = new Steps();
        try {
            $steps = $generator->generate($model, $subject, $task->getGeneratorOptions());
            $this->stepsRecorder->record($steps, $model, $subject, $recorded);
        } catch (Throwable $throwable) {
            $this->messageHelper->createBug($recorded, $throwable->getMessage(), $task->getId(), $task->getModel()->getName());
        } finally {
            $subject->tearDown();

            $this->messageBus->dispatch(new ApplyTaskTransitionMessage($task->getId(), TaskWorkflow::COMPLETE));
        }
    }
}

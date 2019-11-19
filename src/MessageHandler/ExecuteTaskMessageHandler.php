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
use Tienvx\Bundle\MbtBundle\Helper\TokenHelper;
use Tienvx\Bundle\MbtBundle\Helper\WorkflowHelper;
use Tienvx\Bundle\MbtBundle\Message\ApplyTaskTransitionMessage;
use Tienvx\Bundle\MbtBundle\Message\ExecuteTaskMessage;
use Tienvx\Bundle\MbtBundle\Steps\Steps;
use Tienvx\Bundle\MbtBundle\Steps\StepsRecorder;
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
     * @var TokenHelper
     */
    private $tokenHelper;

    /**
     * @var WorkflowHelper
     */
    protected $workflowHelper;

    /**
     * @var MessageBusInterface
     */
    private $messageBus;

    public function __construct(
        SubjectManager $subjectManager,
        GeneratorManager $generatorManager,
        EntityManagerInterface $entityManager,
        MessageHelper $messageHelper,
        TokenHelper $tokenHelper,
        WorkflowHelper $workflowHelper,
        MessageBusInterface $messageBus
    ) {
        $this->subjectManager = $subjectManager;
        $this->generatorManager = $generatorManager;
        $this->entityManager = $entityManager;
        $this->messageHelper = $messageHelper;
        $this->tokenHelper = $tokenHelper;
        $this->workflowHelper = $workflowHelper;
        $this->messageBus = $messageBus;
    }

    /**
     * @throws Exception
     */
    public function __invoke(ExecuteTaskMessage $message)
    {
        $taskId = $message->getId();
        $task = $this->entityManager->find(Task::class, $taskId);

        if (!$task instanceof Task) {
            throw new Exception(sprintf('No task found for id %d', $taskId));
        }

        $subject = $this->subjectManager->createAndSetUp($task->getModel()->getName());
        $generator = $this->generatorManager->get($task->getGenerator()->getName());
        $workflow = $this->workflowHelper->get($task->getModel()->getName());

        $this->tokenHelper->setAnonymousToken();

        $recorded = new Steps();
        try {
            $steps = $generator->generate($workflow, $subject, $task->getGeneratorOptions());
            StepsRecorder::record($steps, $workflow, $subject, $recorded);
        } catch (Throwable $throwable) {
            $this->messageHelper->createBug($recorded, $throwable->getMessage(), $taskId, $task->getModel()->getName());
        } finally {
            $subject->tearDown();

            $this->messageBus->dispatch(new ApplyTaskTransitionMessage($taskId, TaskWorkflow::COMPLETE));
        }
    }
}

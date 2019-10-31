<?php

namespace Tienvx\Bundle\MbtBundle\MessageHandler;

use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;
use Symfony\Component\Messenger\MessageBusInterface;
use Throwable;
use Tienvx\Bundle\MbtBundle\Command\DefaultBugTitleTrait;
use Tienvx\Bundle\MbtBundle\Command\MessageTrait;
use Tienvx\Bundle\MbtBundle\Command\SubjectTrait;
use Tienvx\Bundle\MbtBundle\Command\TokenTrait;
use Tienvx\Bundle\MbtBundle\Command\WorkflowRegisterTrait;
use Tienvx\Bundle\MbtBundle\Entity\Steps;
use Tienvx\Bundle\MbtBundle\Entity\Task;
use Tienvx\Bundle\MbtBundle\Generator\GeneratorManager;
use Tienvx\Bundle\MbtBundle\Helper\StepsRunner;
use Tienvx\Bundle\MbtBundle\Helper\WorkflowHelper;
use Tienvx\Bundle\MbtBundle\Message\ExecuteTaskMessage;
use Tienvx\Bundle\MbtBundle\Subject\SubjectManager;
use Tienvx\Bundle\MbtBundle\Workflow\TaskWorkflow;

class ExecuteTaskMessageHandler implements MessageHandlerInterface
{
    use TokenTrait;
    use SubjectTrait;
    use MessageTrait;
    use WorkflowRegisterTrait;
    use DefaultBugTitleTrait;

    /**
     * @var GeneratorManager
     */
    private $generatorManager;

    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    public function __construct(
        SubjectManager $subjectManager,
        GeneratorManager $generatorManager,
        EntityManagerInterface $entityManager,
        MessageBusInterface $messageBus
    ) {
        $this->subjectManager = $subjectManager;
        $this->generatorManager = $generatorManager;
        $this->entityManager = $entityManager;
        $this->messageBus = $messageBus;
    }

    /**
     * @param ExecuteTaskMessage $message
     *
     * @throws Exception
     */
    public function __invoke(ExecuteTaskMessage $message)
    {
        $taskId = $message->getId();
        $task = $this->entityManager->find(Task::class, $taskId);

        if (!$task instanceof Task) {
            throw new Exception(sprintf('No task found for id %d', $taskId));
        }

        $subject = $this->getSubject($task->getModel()->getName());
        $generator = $this->generatorManager->getGenerator($task->getGenerator()->getName());
        $workflow = WorkflowHelper::get($this->workflowRegistry, $task->getModel()->getName());

        $this->setAnonymousToken();

        $recorded = new Steps();
        try {
            $steps = $generator->generate($workflow, $subject, $task->getGeneratorOptions());
            StepsRunner::record($steps, $workflow, $subject, $recorded);
        } catch (Throwable $throwable) {
            $this->createBug($this->defaultBugTitle, $recorded, $throwable->getMessage(), $taskId, $task->getModel()->getName());
        } finally {
            $subject->tearDown();

            $this->applyTaskTransition($taskId, TaskWorkflow::COMPLETE);
        }
    }
}

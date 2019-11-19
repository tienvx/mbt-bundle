<?php

namespace Tienvx\Bundle\MbtBundle\MessageHandler;

use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;
use Tienvx\Bundle\MbtBundle\Entity\Task;
use Tienvx\Bundle\MbtBundle\Message\ApplyTaskTransitionMessage;
use Tienvx\Bundle\MbtBundle\Workflow\TaskWorkflow;

class ApplyTaskTransitionMessageHandler implements MessageHandlerInterface
{
    /**
     * @var TaskWorkflow
     */
    private $taskWorkflow;

    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager, TaskWorkflow $taskWorkflow)
    {
        $this->entityManager = $entityManager;
        $this->taskWorkflow = $taskWorkflow;
    }

    /**
     * @throws Exception
     */
    public function __invoke(ApplyTaskTransitionMessage $message)
    {
        $taskId = $message->getId();
        $transition = $message->getTransition();
        $task = $this->entityManager->getRepository(Task::class)->find($taskId);

        if (!$task || !$task instanceof Task) {
            throw new Exception(sprintf('No task found for id %d', $taskId));
        }

        $this->taskWorkflow->apply($task, $transition);
        $this->entityManager->flush();
    }
}

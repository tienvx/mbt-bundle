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
     * @var EntityManagerInterface
     */
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function __invoke(ApplyTaskTransitionMessage $message): void
    {
        $taskId = $message->getId();
        $transition = $message->getTransition();
        $task = $this->entityManager->getRepository(Task::class)->find($taskId);

        if (!$task || !$task instanceof Task) {
            throw new Exception(sprintf('No task found for id %d', $taskId));
        }

        $taskWorkflow = new TaskWorkflow();
        $taskWorkflow->apply($task, $transition);
        $this->entityManager->flush();
    }
}

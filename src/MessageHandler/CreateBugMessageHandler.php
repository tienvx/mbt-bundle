<?php

namespace Tienvx\Bundle\MbtBundle\MessageHandler;

use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Tienvx\Bundle\MbtBundle\Entity\Bug;
use Tienvx\Bundle\MbtBundle\Entity\Task;
use Tienvx\Bundle\MbtBundle\Entity\Workflow;
use Tienvx\Bundle\MbtBundle\Helper\WorkflowHelper;
use Tienvx\Bundle\MbtBundle\Message\CreateBugMessage;
use Tienvx\Bundle\MbtBundle\Steps\Steps;

class CreateBugMessageHandler implements MessageHandlerInterface
{
    /**
     * @var ValidatorInterface
     */
    private $validator;

    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     * @var WorkflowHelper
     */
    private $workflowHelper;

    public function __construct(
        EntityManagerInterface $entityManager,
        ValidatorInterface $validator,
        WorkflowHelper $workflowHelper
    ) {
        $this->entityManager = $entityManager;
        $this->validator = $validator;
        $this->workflowHelper = $workflowHelper;
    }

    public function __invoke(CreateBugMessage $message): void
    {
        $bug = $this->initBug($message);

        $errors = $this->validator->validate($bug);

        if (count($errors) > 0) {
            throw new Exception(sprintf('Invalid bug. Reason: %s', (string) $errors));
        }

        $this->entityManager->persist($bug);
        $this->entityManager->flush();
    }

    protected function initBug(CreateBugMessage $message): Bug
    {
        $bug = new Bug();
        $bug->setTitle($message->getTitle());
        $bug->setSteps(Steps::deserialize($message->getSteps()));
        $bug->setWorkflow(new Workflow($message->getWorkflow()));
        $bug->setWorkflowHash($this->workflowHelper->checksum($message->getWorkflow()));
        $bug->setBugMessage($message->getMessage());
        $bug->setStatus($message->getStatus());

        if ($message->getTaskId()) {
            $task = $this->entityManager->getRepository(Task::class)->find($message->getTaskId());
            if ($task instanceof Task) {
                $bug->setTask($task);
            }
        }

        return $bug;
    }
}

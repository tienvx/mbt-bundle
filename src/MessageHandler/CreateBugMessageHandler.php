<?php

namespace Tienvx\Bundle\MbtBundle\MessageHandler;

use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Tienvx\Bundle\MbtBundle\Command\WorkflowRegisterTrait;
use Tienvx\Bundle\MbtBundle\Entity\Bug;
use Tienvx\Bundle\MbtBundle\Entity\Model;
use Tienvx\Bundle\MbtBundle\Entity\Steps;
use Tienvx\Bundle\MbtBundle\Entity\Task;
use Tienvx\Bundle\MbtBundle\Helper\WorkflowHelper;
use Tienvx\Bundle\MbtBundle\Message\CreateBugMessage;

class CreateBugMessageHandler implements MessageHandlerInterface
{
    use WorkflowRegisterTrait;

    /**
     * @var ValidatorInterface
     */
    private $validator;

    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager, ValidatorInterface $validator)
    {
        $this->entityManager = $entityManager;
        $this->validator = $validator;
    }

    /**
     * @param CreateBugMessage $message
     *
     * @throws Exception
     */
    public function __invoke(CreateBugMessage $message)
    {
        $title = $message->getTitle();
        $steps = $message->getSteps();
        $bugMessage = $message->getMessage();
        $taskId = $message->getTaskId();
        $status = $message->getStatus();
        $model = $message->getModel();

        $workflow = WorkflowHelper::get($this->workflowRegistry, $model);

        $bug = new Bug();
        $bug->setTitle($title);
        $bug->setSteps(Steps::deserialize($steps));
        $bug->setModel(new Model($model));
        $bug->setModelHash(WorkflowHelper::checksum($workflow));
        $bug->setBugMessage($bugMessage);
        $bug->setStatus($status);

        if ($taskId) {
            $task = $this->entityManager->getRepository(Task::class)->find($taskId);
            if ($task instanceof Task) {
                $bug->setTask($task);
            }
        }

        $errors = $this->validator->validate($bug);

        if (count($errors) > 0) {
            throw new Exception(sprintf('Invalid bug. Reason: %s', (string) $errors));
        }

        $this->entityManager->persist($bug);
        $this->entityManager->flush();
    }
}

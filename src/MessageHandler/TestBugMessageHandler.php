<?php

namespace Tienvx\Bundle\MbtBundle\MessageHandler;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;
use Symfony\Component\Messenger\MessageBusInterface;
use Throwable;
use Tienvx\Bundle\MbtBundle\Entity\Bug;
use Tienvx\Bundle\MbtBundle\Helper\BugHelper;
use Tienvx\Bundle\MbtBundle\Helper\MessageHelper;
use Tienvx\Bundle\MbtBundle\Helper\Steps\Recorder as StepsRecorder;
use Tienvx\Bundle\MbtBundle\Helper\WorkflowHelper;
use Tienvx\Bundle\MbtBundle\Message\ApplyBugTransitionMessage;
use Tienvx\Bundle\MbtBundle\Message\TestBugMessage;
use Tienvx\Bundle\MbtBundle\Steps\Steps;
use Tienvx\Bundle\MbtBundle\Subject\SubjectManager;
use Tienvx\Bundle\MbtBundle\Workflow\BugWorkflow;

class TestBugMessageHandler implements MessageHandlerInterface
{
    /**
     * @var SubjectManager
     */
    private $subjectManager;

    /**
     * @var EntityManager
     */
    private $entityManager;

    /**
     * @var BugHelper
     */
    private $bugHelper;

    /**
     * @var MessageHelper
     */
    private $messageHelper;

    /**
     * @var WorkflowHelper
     */
    private $workflowHelper;

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
        EntityManagerInterface $entityManager,
        BugHelper $bugHelper,
        MessageHelper $messageHelper,
        WorkflowHelper $workflowHelper,
        MessageBusInterface $messageBus,
        StepsRecorder $stepsRecorder
    ) {
        $this->subjectManager = $subjectManager;
        $this->entityManager = $entityManager;
        $this->bugHelper = $bugHelper;
        $this->messageHelper = $messageHelper;
        $this->workflowHelper = $workflowHelper;
        $this->messageBus = $messageBus;
        $this->stepsRecorder = $stepsRecorder;
    }

    public function __invoke(TestBugMessage $message): void
    {
        $bug = $this->entityManager->find(Bug::class, $message->getBugId());
        $this->validateBug($bug);

        $recorded = new Steps();
        try {
            $workflow = $this->workflowHelper->get($bug->getWorkflow()->getName());
            $subject = $this->subjectManager->create($bug->getWorkflow()->getName());

            $this->stepsRecorder->record($bug->getSteps(), $workflow, $subject, $recorded);
        } catch (Throwable $throwable) {
            $this->handleThrowable($throwable, $bug, $recorded);
        } finally {
            $subject->tearDown();
        }
    }

    protected function handleThrowable(Throwable $throwable, Bug $bug, Steps $recorded): void
    {
        if ($throwable->getMessage() === $bug->getBugMessage()) {
            if ($recorded->getLength() < $bug->getSteps()->getLength()) {
                $this->bugHelper->updateSteps($bug, $recorded);
            }
            $this->messageBus->dispatch(new ApplyBugTransitionMessage($bug->getId(), BugWorkflow::REOPEN));
        } else {
            $this->messageHelper->createBug($recorded, $throwable->getMessage(), null, $bug->getWorkflow()->getName());
        }
    }

    protected function validateBug(Bug $bug): void
    {
        if (!$bug instanceof Bug) {
            throw new Exception(sprintf('No bug found for id %d', $bug->getId()));
        }

        if (BugWorkflow::CLOSED !== $bug->getStatus()) {
            throw new Exception(sprintf('Can not test bug with id %d, only closed bug can be tested again', $bug->getId()));
        }

        if ($this->workflowHelper->checksum($bug->getWorkflow()->getName()) !== $bug->getWorkflowHash()) {
            throw new Exception(sprintf('Model checksum of bug with id %d does not match', $bug->getId()));
        }
    }
}

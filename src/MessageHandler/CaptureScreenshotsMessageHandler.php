<?php

namespace Tienvx\Bundle\MbtBundle\MessageHandler;

use Doctrine\ORM\EntityManagerInterface;
use Exception;
use League\Flysystem\FilesystemInterface;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;
use Symfony\Component\Workflow\Workflow;
use Tienvx\Bundle\MbtBundle\Entity\Bug;
use Tienvx\Bundle\MbtBundle\Helper\TokenHelper;
use Tienvx\Bundle\MbtBundle\Helper\WorkflowHelper;
use Tienvx\Bundle\MbtBundle\Message\CaptureScreenshotsMessage;
use Tienvx\Bundle\MbtBundle\Steps\StepsCapturer;
use Tienvx\Bundle\MbtBundle\Subject\SubjectInterface;
use Tienvx\Bundle\MbtBundle\Subject\SubjectManager;
use Tienvx\Bundle\MbtBundle\Subject\SubjectScreenshotInterface;

class CaptureScreenshotsMessageHandler implements MessageHandlerInterface
{
    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     * @var SubjectManager
     */
    private $subjectManager;

    /**
     * @var FilesystemInterface
     */
    private $mbtStorage;

    /**
     * @var TokenHelper
     */
    private $tokenHelper;

    /**
     * @var WorkflowHelper
     */
    private $workflowHelper;

    public function __construct(
        EntityManagerInterface $entityManager,
        SubjectManager $subjectManager,
        FilesystemInterface $mbtStorage,
        TokenHelper $tokenHelper,
        WorkflowHelper $workflowHelper
    ) {
        $this->entityManager = $entityManager;
        $this->subjectManager = $subjectManager;
        $this->mbtStorage = $mbtStorage;
        $this->tokenHelper = $tokenHelper;
        $this->workflowHelper = $workflowHelper;
    }

    public function __invoke(CaptureScreenshotsMessage $message): void
    {
        $bugId = $message->getBugId();
        $bug = $this->entityManager->getRepository(Bug::class)->find($bugId);
        $model = $bug->getModel()->getName();

        if (!$bug || !$bug instanceof Bug) {
            throw new Exception(sprintf('No bug found for id %d', $bugId));
        }

        $workflow = $this->workflowHelper->get($model);
        if ($this->workflowHelper->checksum($workflow) !== $bug->getModelHash()) {
            throw new Exception(sprintf('Model checksum of bug with id %d does not match', $bugId));
        }

        $subject = $this->subjectManager->createAndSetUp($model);

        $this->capture($subject, $bug, $workflow);
    }

    protected function capture(SubjectInterface $subject, Bug $bug, Workflow $workflow): void
    {
        if (!$subject instanceof SubjectScreenshotInterface) {
            throw new Exception(sprintf('Cannot capture screenshots for bug with id "%d"! Class %s must implements interface %s', $bug->getId(), get_class($subject), SubjectScreenshotInterface::class));
        }

        $subject->setFilesystem($this->mbtStorage);
        $subject->removeScreenshots($bug->getId());

        $this->tokenHelper->setAnonymousToken();

        StepsCapturer::capture($bug->getSteps(), $workflow, $subject, $bug->getId());
    }
}

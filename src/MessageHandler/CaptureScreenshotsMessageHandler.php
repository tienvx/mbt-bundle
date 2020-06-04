<?php

namespace Tienvx\Bundle\MbtBundle\MessageHandler;

use Doctrine\ORM\EntityManagerInterface;
use Exception;
use League\Flysystem\FilesystemInterface;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;
use Tienvx\Bundle\MbtBundle\Entity\Bug;
use Tienvx\Bundle\MbtBundle\Helper\Steps\ScreenshotsCapturer;
use Tienvx\Bundle\MbtBundle\Helper\WorkflowHelper;
use Tienvx\Bundle\MbtBundle\Message\CaptureScreenshotsMessage;
use Tienvx\Bundle\MbtBundle\Model\Subject\ScreenshotInterface;
use Tienvx\Bundle\MbtBundle\Model\SubjectInterface;
use Tienvx\Bundle\MbtBundle\Subject\SubjectManager;

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
     * @var WorkflowHelper
     */
    private $workflowHelper;

    /**
     * @var ScreenshotsCapturer
     */
    private $screenshotsCapturer;

    public function __construct(
        EntityManagerInterface $entityManager,
        SubjectManager $subjectManager,
        WorkflowHelper $workflowHelper,
        ScreenshotsCapturer $screenshotsCapturer
    ) {
        $this->entityManager = $entityManager;
        $this->subjectManager = $subjectManager;
        $this->workflowHelper = $workflowHelper;
        $this->screenshotsCapturer = $screenshotsCapturer;
    }

    public function __invoke(CaptureScreenshotsMessage $message): void
    {
        if (!$this->mbtStorage instanceof FilesystemInterface) {
            throw new Exception('Storage "mbt.storage" is missing');
        }

        $bugId = $message->getBugId();
        $bug = $this->entityManager->getRepository(Bug::class)->find($bugId);
        $workflow = $bug->getWorkflow()->getName();

        if (!$bug || !$bug instanceof Bug) {
            throw new Exception(sprintf('No bug found for id %d', $bugId));
        }

        if ($this->workflowHelper->checksum($workflow) !== $bug->getWorkflowHash()) {
            throw new Exception(sprintf('Model checksum of bug with id %d does not match', $bugId));
        }

        $subject = $this->subjectManager->createAndSetUp($workflow);

        $this->capture($subject, $bug);
    }

    public function setMbtStorage(FilesystemInterface $mbtStorage): void
    {
        $this->mbtStorage = $mbtStorage;
    }

    protected function capture(SubjectInterface $subject, Bug $bug): void
    {
        if (!$subject instanceof ScreenshotInterface) {
            throw new Exception(sprintf('Cannot capture screenshots for bug with id "%d"! Class %s must implements interface %s', $bug->getId(), get_class($subject), ScreenshotInterface::class));
        }

        $subject->setFilesystem($this->mbtStorage);
        $subject->removeScreenshots($bug->getId());

        $workflow = $this->workflowHelper->get($bug->getWorkflow()->getName());
        $this->screenshotsCapturer->capture($bug->getSteps(), $workflow, $subject, $bug->getId());
    }
}

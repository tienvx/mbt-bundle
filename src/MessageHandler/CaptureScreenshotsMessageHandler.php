<?php

namespace Tienvx\Bundle\MbtBundle\MessageHandler;

use Doctrine\ORM\EntityManagerInterface;
use Exception;
use League\Flysystem\FilesystemInterface;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;
use Tienvx\Bundle\MbtBundle\Entity\Bug;
use Tienvx\Bundle\MbtBundle\Helper\TokenHelper;
use Tienvx\Bundle\MbtBundle\Helper\WorkflowHelper;
use Tienvx\Bundle\MbtBundle\Message\CaptureScreenshotsMessage;
use Tienvx\Bundle\MbtBundle\Steps\StepsCapturer;
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

        if (!$bug || !$bug instanceof Bug) {
            throw new Exception(sprintf('No bug found for id %d', $bugId));
        }

        $workflow = $this->workflowHelper->get($bug->getModel()->getName());
        if ($this->workflowHelper->checksum($workflow) !== $bug->getModelHash()) {
            throw new Exception(sprintf('Model checksum of bug with id %d does not match', $bugId));
        }

        $subject = $this->subjectManager->createAndSetUp($bug->getModel()->getName());
        $subject->setFilesystem($this->mbtStorage);
        $subject->removeScreenshots($bug->getId());

        $this->tokenHelper->setAnonymousToken();

        StepsCapturer::capture($bug->getSteps(), $workflow, $subject, $bugId);
    }
}

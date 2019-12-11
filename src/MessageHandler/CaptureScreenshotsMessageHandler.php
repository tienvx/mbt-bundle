<?php

namespace Tienvx\Bundle\MbtBundle\MessageHandler;

use Doctrine\ORM\EntityManagerInterface;
use Exception;
use League\Flysystem\FilesystemInterface;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;
use Tienvx\Bundle\MbtBundle\Entity\Bug;
use Tienvx\Bundle\MbtBundle\Helper\ModelHelper;
use Tienvx\Bundle\MbtBundle\Helper\Steps\ScreenshotsCapturer;
use Tienvx\Bundle\MbtBundle\Message\CaptureScreenshotsMessage;
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
     * @var ModelHelper
     */
    private $modelHelper;

    /**
     * @var ScreenshotsCapturer
     */
    private $screenshotsCapturer;

    public function __construct(
        EntityManagerInterface $entityManager,
        SubjectManager $subjectManager,
        FilesystemInterface $mbtStorage,
        ModelHelper $modelHelper,
        ScreenshotsCapturer $screenshotsCapturer
    ) {
        $this->entityManager = $entityManager;
        $this->subjectManager = $subjectManager;
        $this->mbtStorage = $mbtStorage;
        $this->modelHelper = $modelHelper;
        $this->screenshotsCapturer = $screenshotsCapturer;
    }

    public function __invoke(CaptureScreenshotsMessage $message): void
    {
        $bugId = $message->getBugId();
        $bug = $this->entityManager->getRepository(Bug::class)->find($bugId);
        $model = $bug->getModel()->getName();

        if (!$bug || !$bug instanceof Bug) {
            throw new Exception(sprintf('No bug found for id %d', $bugId));
        }

        if ($this->modelHelper->checksum($model) !== $bug->getModelHash()) {
            throw new Exception(sprintf('Model checksum of bug with id %d does not match', $bugId));
        }

        $subject = $this->subjectManager->createAndSetUp($model);

        $this->capture($subject, $bug);
    }

    protected function capture(SubjectInterface $subject, Bug $bug): void
    {
        if (!$subject instanceof SubjectScreenshotInterface) {
            throw new Exception(sprintf('Cannot capture screenshots for bug with id "%d"! Class %s must implements interface %s', $bug->getId(), get_class($subject), SubjectScreenshotInterface::class));
        }

        $subject->setFilesystem($this->mbtStorage);
        $subject->removeScreenshots($bug->getId());

        $model = $this->modelHelper->get($bug->getModel()->getName());
        $this->screenshotsCapturer->capture($bug->getSteps(), $model, $subject, $bug->getId());
    }
}

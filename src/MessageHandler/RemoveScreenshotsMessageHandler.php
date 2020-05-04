<?php

namespace Tienvx\Bundle\MbtBundle\MessageHandler;

use Exception;
use League\Flysystem\FilesystemInterface;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;
use Tienvx\Bundle\MbtBundle\Message\RemoveScreenshotsMessage;
use Tienvx\Bundle\MbtBundle\Model\Subject\ScreenshotInterface;
use Tienvx\Bundle\MbtBundle\Model\SubjectInterface;
use Tienvx\Bundle\MbtBundle\Subject\SubjectManager;

class RemoveScreenshotsMessageHandler implements MessageHandlerInterface
{
    /**
     * @var SubjectManager
     */
    protected $subjectManager;

    /**
     * @var FilesystemInterface
     */
    protected $mbtStorage;

    public function __construct(SubjectManager $subjectManager)
    {
        $this->subjectManager = $subjectManager;
    }

    public function setMbtStorage(FilesystemInterface $mbtStorage): void
    {
        $this->mbtStorage = $mbtStorage;
    }

    public function __invoke(RemoveScreenshotsMessage $message): void
    {
        if (!$this->mbtStorage instanceof FilesystemInterface) {
            throw new Exception('Storage "mbt.storage" is missing');
        }

        $bugId = $message->getBugId();
        $workflow = $message->getWorkflow();

        $subject = $this->subjectManager->create($workflow);

        $this->removeScreenshots($subject, $bugId);
    }

    protected function removeScreenshots(SubjectInterface $subject, int $bugId): void
    {
        if (!$subject instanceof ScreenshotInterface) {
            throw new Exception(sprintf('Cannot capture screenshots for bug with id "%d"! Class %s must implements interface %s', $bugId, get_class($subject), ScreenshotInterface::class));
        }

        $subject->setFilesystem($this->mbtStorage);
        $subject->removeScreenshots($bugId);
    }
}

<?php

namespace Tienvx\Bundle\MbtBundle\MessageHandler;

use Exception;
use League\Flysystem\FilesystemInterface;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;
use Tienvx\Bundle\MbtBundle\Message\RemoveScreenshotsMessage;
use Tienvx\Bundle\MbtBundle\Subject\SubjectInterface;
use Tienvx\Bundle\MbtBundle\Subject\SubjectManager;
use Tienvx\Bundle\MbtBundle\Subject\SubjectScreenshotInterface;

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

    public function __construct(
        SubjectManager $subjectManager,
        FilesystemInterface $mbtStorage
    ) {
        $this->subjectManager = $subjectManager;
        $this->mbtStorage = $mbtStorage;
    }

    public function __invoke(RemoveScreenshotsMessage $message): void
    {
        $bugId = $message->getBugId();
        $model = $message->getModel();

        $subject = $this->subjectManager->create($model);

        $this->removeScreenshots($subject, $bugId);
    }

    protected function removeScreenshots(SubjectInterface $subject, int $bugId): void
    {
        if (!$subject instanceof SubjectScreenshotInterface) {
            throw new Exception(sprintf('Cannot capture screenshots for bug with id "%d"! Class %s must implements interface %s', $bugId, get_class($subject), SubjectScreenshotInterface::class));
        }

        $subject->setFilesystem($this->mbtStorage);
        $subject->removeScreenshots($bugId);
    }
}

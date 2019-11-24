<?php

namespace Tienvx\Bundle\MbtBundle\MessageHandler;

use League\Flysystem\FilesystemInterface;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;
use Tienvx\Bundle\MbtBundle\Message\RemoveScreenshotsMessage;
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
        $subject->setFilesystem($this->mbtStorage);
        $subject->removeScreenshots($bugId);
    }
}

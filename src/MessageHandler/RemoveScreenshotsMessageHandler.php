<?php

namespace Tienvx\Bundle\MbtBundle\MessageHandler;

use Doctrine\ORM\EntityManagerInterface;
use Exception;
use League\Flysystem\FilesystemInterface;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;
use Tienvx\Bundle\MbtBundle\Message\RemoveScreenshotsMessage;
use Tienvx\Bundle\MbtBundle\Subject\SubjectManager;

class RemoveScreenshotsMessageHandler implements MessageHandlerInterface
{
    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     * @var SubjectManager
     */
    protected $subjectManager;

    /**
     * @var FilesystemInterface
     */
    protected $mbtStorage;

    public function __construct(
        EntityManagerInterface $entityManager,
        SubjectManager $subjectManager,
        FilesystemInterface $mbtStorage
    ) {
        $this->entityManager = $entityManager;
        $this->subjectManager = $subjectManager;
        $this->mbtStorage = $mbtStorage;
    }

    /**
     * @param RemoveScreenshotsMessage $message
     *
     * @throws Exception
     */
    public function __invoke(RemoveScreenshotsMessage $message)
    {
        $bugId = $message->getBugId();
        $model = $message->getModel();

        $subject = $this->subjectManager->createSubject($model);
        $subject->setFilesystem($this->mbtStorage);
        $subject->removeScreenshots($bugId);
    }
}

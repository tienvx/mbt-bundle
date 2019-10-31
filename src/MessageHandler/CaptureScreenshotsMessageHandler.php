<?php

namespace Tienvx\Bundle\MbtBundle\MessageHandler;

use Doctrine\ORM\EntityManagerInterface;
use Exception;
use League\Flysystem\FilesystemInterface;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;
use Throwable;
use Tienvx\Bundle\MbtBundle\Command\WorkflowRegisterTrait;
use Tienvx\Bundle\MbtBundle\Command\SubjectTrait;
use Tienvx\Bundle\MbtBundle\Command\TokenTrait;
use Tienvx\Bundle\MbtBundle\Entity\Bug;
use Tienvx\Bundle\MbtBundle\Entity\Data;
use Tienvx\Bundle\MbtBundle\Helper\WorkflowHelper;
use Tienvx\Bundle\MbtBundle\Message\CaptureScreenshotsMessage;
use Tienvx\Bundle\MbtBundle\Subject\SubjectManager;

class CaptureScreenshotsMessageHandler implements MessageHandlerInterface
{
    use TokenTrait;
    use SubjectTrait;
    use WorkflowRegisterTrait;

    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

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
     * @param CaptureScreenshotsMessage $message
     *
     * @throws Exception
     */
    public function __invoke(CaptureScreenshotsMessage $message)
    {
        $bugId = $message->getBugId();
        $bug = $this->entityManager->getRepository(Bug::class)->find($bugId);

        if (!$bug || !$bug instanceof Bug) {
            throw new Exception(sprintf('No bug found for id %d', $bugId));
        }

        $workflow = WorkflowHelper::get($this->workflowRegistry, $bug->getModel()->getName());
        if (WorkflowHelper::checksum($workflow) !== $bug->getModelHash()) {
            throw new Exception(sprintf('Model checksum of bug with id %d does not match', $bugId));
        }

        $subject = $this->getSubject($bug->getModel()->getName());
        $subject->setFilesystem($this->mbtStorage);
        $subject->removeScreenshots($bug->getId());

        $this->setAnonymousToken();

        try {
            foreach ($bug->getSteps() as $index => $step) {
                if ($step->getTransition() && $step->getData() instanceof Data) {
                    try {
                        $workflow->apply($subject, $step->getTransition(), [
                            'data' => $step->getData(),
                        ]);
                    } catch (Throwable $throwable) {
                    } finally {
                        $subject->captureScreenshot($bugId, $index);
                    }
                } elseif (0 === $index) {
                    $subject->captureScreenshot($bugId, $index);
                }
            }
        } finally {
            $subject->tearDown();
        }
    }
}

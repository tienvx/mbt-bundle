<?php

namespace Tienvx\Bundle\MbtBundle\MessageHandler;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;
use Tienvx\Bundle\MbtBundle\Entity\Bug;
use Tienvx\Bundle\MbtBundle\Exception\ExceptionInterface;
use Tienvx\Bundle\MbtBundle\Exception\UnexpectedValueException;
use Tienvx\Bundle\MbtBundle\Message\ReportBugMessage;
use Tienvx\Bundle\MbtBundle\Model\BugInterface;
use Tienvx\Bundle\MbtBundle\Service\NotifyHelperInterface;

class ReportBugMessageHandler implements MessageHandlerInterface
{
    protected EntityManagerInterface $entityManager;
    protected NotifyHelperInterface $notifyHelper;

    public function __construct(EntityManagerInterface $entityManager, NotifyHelperInterface $notifyHelper)
    {
        $this->entityManager = $entityManager;
        $this->notifyHelper = $notifyHelper;
    }

    /**
     * @throws ExceptionInterface
     */
    public function __invoke(ReportBugMessage $message): void
    {
        $bugId = $message->getBugId();

        $bug = $this->entityManager->find(Bug::class, $bugId);

        if (!$bug instanceof BugInterface) {
            throw new UnexpectedValueException(sprintf('Can not report bug %d: bug not found', $bugId));
        }

        if ($bug->getTask()->getTaskConfig()->getNotifyChannels()) {
            $this->notifyHelper->notify($bug);
        }
    }
}

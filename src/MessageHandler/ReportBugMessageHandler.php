<?php

namespace Tienvx\Bundle\MbtBundle\MessageHandler;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;
use Symfony\Component\Notifier\NotifierInterface;
use Symfony\Component\Notifier\Recipient\Recipient;
use Symfony\Contracts\Translation\TranslatorInterface;
use Tienvx\Bundle\MbtBundle\Entity\Bug;
use Tienvx\Bundle\MbtBundle\Exception\ExceptionInterface;
use Tienvx\Bundle\MbtBundle\Exception\UnexpectedValueException;
use Tienvx\Bundle\MbtBundle\Message\ReportBugMessage;
use Tienvx\Bundle\MbtBundle\Model\BugInterface;
use Tienvx\Bundle\MbtBundle\Model\TaskInterface;
use Tienvx\Bundle\MbtBundle\Notification\BugNotification;
use Tienvx\Bundle\MbtBundle\Service\BugHelperInterface;
use Tienvx\Bundle\MbtBundle\Service\ConfigLoaderInterface;

/**
 * Override this service to customize notification.
 */
class ReportBugMessageHandler implements MessageHandlerInterface
{
    protected EntityManagerInterface $entityManager;
    protected NotifierInterface $notifier;
    protected ConfigLoaderInterface $configLoader;
    protected BugHelperInterface $bugHelper;
    protected TranslatorInterface $translator;

    public function __construct(
        EntityManagerInterface $entityManager,
        NotifierInterface $notifier,
        ConfigLoaderInterface $configLoader,
        BugHelperInterface $bugHelper,
        TranslatorInterface $translator
    ) {
        $this->entityManager = $entityManager;
        $this->notifier = $notifier;
        $this->configLoader = $configLoader;
        $this->bugHelper = $bugHelper;
        $this->translator = $translator;
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

        $this->sendNotification($bug);
    }

    protected function sendNotification(BugInterface $bug): void
    {
        $notification = new BugNotification($bug->getTitle(), $this->configLoader->getNotifyChannels());
        $notification->setBugUrl($this->bugHelper->buildBugUrl($bug));
        $notification->content(implode("\n", [
            $this->translator->trans('mbt.notify.bug_id', ['id' => $bug->getId()]),
            $this->translator->trans('mbt.notify.bug_message', ['message' => $bug->getMessage()]),
            $this->translator->trans('mbt.notify.more_info'),
        ]));
        $notification->emoji(':bug:');

        $this->notifier->send($notification, ...$this->getRecipients($bug->getTask()));
    }

    protected function getRecipients(TaskInterface $task): array
    {
        $recipients = [];
        $email = $task->getUser()->getUsername();
        if ($task->getSendEmail() && filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $recipients[] = new Recipient($email);
        }

        return $recipients;
    }
}

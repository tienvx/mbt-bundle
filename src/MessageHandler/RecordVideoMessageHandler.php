<?php

namespace Tienvx\Bundle\MbtBundle\MessageHandler;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;
use Symfony\Component\Messenger\MessageBusInterface;
use Tienvx\Bundle\MbtBundle\Entity\Bug;
use Tienvx\Bundle\MbtBundle\Exception\ExceptionInterface;
use Tienvx\Bundle\MbtBundle\Exception\UnexpectedValueException;
use Tienvx\Bundle\MbtBundle\Message\DownloadVideoMessage;
use Tienvx\Bundle\MbtBundle\Message\RecordVideoMessage;
use Tienvx\Bundle\MbtBundle\Model\BugInterface;
use Tienvx\Bundle\MbtBundle\Provider\ProviderManager;
use Tienvx\Bundle\MbtBundle\Service\StepsRunnerInterface;

class RecordVideoMessageHandler implements MessageHandlerInterface
{
    protected ProviderManager $providerManager;
    protected EntityManagerInterface $entityManager;
    protected StepsRunnerInterface $stepsRunner;
    protected MessageBusInterface $messageBus;

    public function __construct(
        ProviderManager $providerManager,
        EntityManagerInterface $entityManager,
        StepsRunnerInterface $stepsRunner,
        MessageBusInterface $messageBus
    ) {
        $this->providerManager = $providerManager;
        $this->entityManager = $entityManager;
        $this->stepsRunner = $stepsRunner;
        $this->messageBus = $messageBus;
    }

    /**
     * @throws ExceptionInterface
     */
    public function __invoke(RecordVideoMessage $message): void
    {
        $bugId = $message->getBugId();
        $bug = $this->entityManager->find(Bug::class, $bugId);

        if (!$bug instanceof BugInterface) {
            throw new UnexpectedValueException(sprintf('Can not record video for bug %d: bug not found', $bugId));
        }

        $this->execute($bug);
    }

    /**
     * @throws ExceptionInterface
     */
    protected function execute(BugInterface $bug): void
    {
        try {
            $this->stepsRunner->run($bug->getSteps(), $bug->getTask(), $bug->getId());
        } catch (ExceptionInterface $exception) {
            throw $exception;
        } finally {
            $provider = $this->providerManager->get($bug->getTask()->getSeleniumConfig()->getProvider());
            $this->messageBus->dispatch(new DownloadVideoMessage(
                $bug->getId(),
                $provider->getVideoUrl($this->providerManager->getSeleniumServer(), $bug->getId())
            ));
        }
    }
}

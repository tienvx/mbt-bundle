<?php

namespace Tienvx\Bundle\MbtBundle\MessageHandler;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;
use Throwable;
use Tienvx\Bundle\MbtBundle\Entity\Bug;
use Tienvx\Bundle\MbtBundle\Exception\ExceptionInterface;
use Tienvx\Bundle\MbtBundle\Exception\UnexpectedValueException;
use Tienvx\Bundle\MbtBundle\Message\RecordVideoMessage;
use Tienvx\Bundle\MbtBundle\Model\BugInterface;
use Tienvx\Bundle\MbtBundle\Provider\ProviderManager;
use Tienvx\Bundle\MbtBundle\Service\StepRunnerInterface;

class RecordVideoMessageHandler implements MessageHandlerInterface
{
    protected ProviderManager $providerManager;
    protected EntityManagerInterface $entityManager;
    protected StepRunnerInterface $stepRunner;

    public function __construct(
        ProviderManager $providerManager,
        EntityManagerInterface $entityManager,
        StepRunnerInterface $stepRunner
    ) {
        $this->providerManager = $providerManager;
        $this->entityManager = $entityManager;
        $this->stepRunner = $stepRunner;
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
        $driver = $this->providerManager->createDriver($bug->getTask(), $bug->getId());
        try {
            foreach ($bug->getSteps() as $step) {
                $this->stepRunner->run($step, $bug->getTask()->getModelRevision(), $driver);
            }
        } catch (ExceptionInterface $exception) {
            throw $exception;
        } catch (Throwable $throwable) {
            // Do nothing.
        } finally {
            $driver->quit();
        }
    }
}

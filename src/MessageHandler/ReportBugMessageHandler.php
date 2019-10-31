<?php

namespace Tienvx\Bundle\MbtBundle\MessageHandler;

use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;
use Tienvx\Bundle\MbtBundle\Entity\Bug;
use Tienvx\Bundle\MbtBundle\Message\ReportBugMessage;
use Tienvx\Bundle\MbtBundle\Reporter\ReporterManager;

class ReportBugMessageHandler implements MessageHandlerInterface
{
    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     * @var ReporterManager
     */
    protected $reporterManager;

    public function __construct(
        EntityManagerInterface $entityManager,
        ReporterManager $reporterManager
    ) {
        $this->entityManager = $entityManager;
        $this->reporterManager = $reporterManager;
    }

    /**
     * @param ReportBugMessage $message
     *
     * @throws Exception
     */
    public function __invoke(ReportBugMessage $message)
    {
        $bugId = $message->getBugId();
        $reporter = $message->getReporter();

        $bug = $this->entityManager->find(Bug::class, $bugId);

        if (!$bug instanceof Bug) {
            throw new Exception(sprintf('No bug found for id %d', $bugId));
        }

        $reporterService = $this->reporterManager->getReporter($reporter);
        $reporterService->report($bug);
    }
}

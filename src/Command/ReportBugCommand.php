<?php

namespace Tienvx\Bundle\MbtBundle\Command;

use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Tienvx\Bundle\MbtBundle\Entity\Bug;
use Tienvx\Bundle\MbtBundle\Reporter\ReporterManager;

class ReportBugCommand extends Command
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

        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->setName('mbt:bug:report')
            ->setDescription('Report a bug.')
            ->setHelp('Report a bug to email, hipchat or jira.')
            ->addArgument('bug-id', InputArgument::REQUIRED, 'The bug id to report.');
    }

    /**
     * @param InputInterface  $input
     * @param OutputInterface $output
     *
     * @throws Exception
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $bugId = $input->getArgument('bug-id');

        $callback = function () use ($bugId) {
            $bug = $this->entityManager->find(Bug::class, $bugId);

            if ($bug instanceof Bug) {
                $bug->setStatus('reported');
            }

            return $bug;
        };

        $bug = $this->entityManager->transactional($callback);

        if (!$bug instanceof Bug) {
            $output->writeln(sprintf('No bug found for id %d', $bugId));

            return;
        }

        foreach ($bug->getTask()->getReporters() as $reporter) {
            $reporterPlugin = $this->reporterManager->getReporter($reporter->getName());
            $reporterPlugin->report($bug);
        }
    }
}

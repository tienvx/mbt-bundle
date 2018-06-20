<?php

namespace Tienvx\Bundle\MbtBundle\Command;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Tienvx\Bundle\MbtBundle\Entity\Bug;
use Tienvx\Bundle\MbtBundle\Service\ReporterManager;

class ReportBugCommand extends Command
{
    private $reporterManager;
    private $entityManager;

    public function __construct(ReporterManager $reporterManager, EntityManagerInterface $entityManager)
    {
        $this->reporterManager = $reporterManager;
        $this->entityManager = $entityManager;

        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->setName('mbt:report-bug')
            ->setDescription('Report a bug.')
            ->setHelp('Report a bug to email, hipchat or jira.')
            ->addArgument('bug-id', InputArgument::REQUIRED, 'The bug id to report.');
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @throws \Exception
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $bugId = $input->getArgument('bug-id');
        /** @var Bug $bug */
        $bug = $this->entityManager->getRepository(Bug::class)->find($bugId);

        if (!$bug) {
            $output->writeln(sprintf('No bug found for id %d', $bugId));
            return;
        }

        $reporter = $this->reporterManager->getReporter($bug->getReproducePath()->getTask()->getReporter());
        $reporter->report($bug);
    }
}

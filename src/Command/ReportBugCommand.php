<?php

namespace Tienvx\Bundle\MbtBundle\Command;

use Exception;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Tienvx\Bundle\MbtBundle\Entity\Bug;
use Tienvx\Bundle\MbtBundle\Helper\PathBuilder;
use Tienvx\Bundle\MbtBundle\Subject\SubjectManager;

class ReportBugCommand extends Command
{
    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var SubjectManager
     */
    protected $subjectManager;

    public function __construct(
        EntityManagerInterface $entityManager,
        LoggerInterface $logger,
        SubjectManager $subjectManager
    ) {
        $this->entityManager = $entityManager;
        $this->logger = $logger;
        $this->subjectManager = $subjectManager;

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
     * @param InputInterface $input
     * @param OutputInterface $output
     * @throws Exception
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

        $bug->setStatus('reported');
        $this->entityManager->flush();

        $path = PathBuilder::build($bug->getPath());
        $model = $bug->getTask()->getModel();
        $subject = $this->subjectManager->createSubject($model);

        $this->logger->error($bug->getBugMessage(), [
            'bug' => $bug,
            'path' => $path,
            'model' => $model,
            'subject' => $subject,
        ]);
    }
}

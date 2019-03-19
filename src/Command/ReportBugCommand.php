<?php

namespace Tienvx\Bundle\MbtBundle\Command;

use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Tienvx\Bundle\MbtBundle\Entity\Bug;
use Tienvx\Bundle\MbtBundle\Graph\Path;
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
        $this->entityManager  = $entityManager;
        $this->logger         = $logger;
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

        $path = Path::unserialize($bug->getPath());
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

<?php

namespace Tienvx\Bundle\MbtBundle\Command;

use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Messenger\MessageBusInterface;
use Tienvx\Bundle\MbtBundle\Entity\Bug;
use Tienvx\Bundle\MbtBundle\Entity\Task;
use Tienvx\Bundle\MbtBundle\Workflow\BugWorkflow;

class FinishReduceBugCommand extends Command
{
    use MessageTrait;

    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    public function __construct(
        EntityManagerInterface $entityManager,
        MessageBusInterface $messageBus
    ) {
        $this->entityManager = $entityManager;
        $this->messageBus = $messageBus;

        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->setName('mbt:bug:finish-reduce')
            ->setDescription('Finish reduce bug.')
            ->setHelp('Do things after finish reducing bugs: report bug, capture screenshots')
            ->addArgument('bug-id', InputArgument::REQUIRED, 'The bug id.')
            ->setHidden(true);
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
        $bug = $this->entityManager->find(Bug::class, $bugId);

        if (!$bug instanceof Bug) {
            throw new Exception(sprintf('No bug found for id %d', $bugId));
        }

        $this->applyBugTransition($bug->getId(), BugWorkflow::COMPLETE_REDUCE);

        $task = $bug->getTask();
        if ($task instanceof Task) {
            if (!empty($task->getReporters())) {
                foreach ($task->getReporters() as $reporter) {
                    $this->reportBug($bug->getId(), $reporter->getName());
                }
            }
            if ($task->getTakeScreenshots()) {
                $this->captureScreenshots($bug->getId());
            }
        }
    }
}

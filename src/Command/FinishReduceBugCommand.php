<?php

namespace Tienvx\Bundle\MbtBundle\Command;

use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Messenger\MessageBusInterface;
use Tienvx\Bundle\MbtBundle\Entity\Bug;
use Tienvx\Bundle\MbtBundle\Entity\Task;
use Tienvx\Bundle\MbtBundle\Message\ApplyBugTransitionMessage;
use Tienvx\Bundle\MbtBundle\Message\CaptureScreenshotsMessage;
use Tienvx\Bundle\MbtBundle\Message\ReportBugMessage;
use Tienvx\Bundle\MbtBundle\Workflow\BugWorkflow;

class FinishReduceBugCommand extends AbstractCommand
{
    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     * @var MessageBusInterface
     */
    private $messageBus;

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
            ->addArgument('bug-id', InputArgument::REQUIRED, 'The bug id.');
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
            $output->writeln(sprintf('No bug found for id %d', $bugId));

            return;
        }

        $task = $bug->getTask();
        if (!$task instanceof Task) {
            $output->writeln(sprintf('Task of bug with id %d is missing', $bugId));

            return;
        }

        $this->messageBus->dispatch(new ApplyBugTransitionMessage($bug->getId(), BugWorkflow::COMPLETE_REDUCE));

        if (!empty($task->getReporters())) {
            foreach ($task->getReporters() as $reporter) {
                $this->messageBus->dispatch(new ReportBugMessage($bug->getId(), $reporter->getName()));
            }
        }
        if ($task->getTakeScreenshots()) {
            $this->messageBus->dispatch(new CaptureScreenshotsMessage($bug->getId()));
        }
    }
}

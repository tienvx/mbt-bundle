<?php

namespace Tienvx\Bundle\MbtBundle\Command;

use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Tienvx\Bundle\MbtBundle\Entity\Bug;
use Tienvx\Bundle\MbtBundle\Workflow\BugWorkflow;

class ApplyBugTransitionCommand extends AbstractCommand
{
    /**
     * @var BugWorkflow
     */
    private $bugWorkflow;

    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager, BugWorkflow $bugWorkflow)
    {
        $this->entityManager = $entityManager;
        $this->bugWorkflow = $bugWorkflow;

        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->setName('mbt:bug:apply-transition')
            ->setDescription('Apply transition of a bug.')
            ->setHelp("This command change status of a bug by applying a transition of bug's workflow.")
            ->addArgument('bug-id', InputArgument::REQUIRED, 'The bug id to update.')
            ->addArgument('transition', InputArgument::REQUIRED, 'The transition to apply.');
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
        $bug = $this->entityManager->getRepository(Bug::class)->find($bugId);

        if (!$bug || !$bug instanceof Bug) {
            $output->writeln(sprintf('No bug found for id %d', $bugId));

            return;
        }

        $transition = $input->getArgument('transition');

        $this->bugWorkflow->apply($bug, $transition);
        $this->entityManager->flush();
    }
}

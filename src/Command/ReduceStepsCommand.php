<?php

namespace Tienvx\Bundle\MbtBundle\Command;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Tienvx\Bundle\MbtBundle\Entity\Bug;
use Tienvx\Bundle\MbtBundle\PathReducer\PathReducerManager;

class ReduceStepsCommand extends AbstractCommand
{
    private $pathReducerManager;
    private $entityManager;

    public function __construct(PathReducerManager $pathReducerManager, EntityManagerInterface $entityManager)
    {
        $this->pathReducerManager = $pathReducerManager;
        $this->entityManager = $entityManager;

        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->setName('mbt:reduce-steps')
            ->setDescription('Reduce a reproduce steps.')
            ->setHelp("Make bug's reproduce steps shorter.")
            ->addArgument('bug-id', InputArgument::REQUIRED, 'The bug id to reduce the steps.');
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

        $this->setAnonymousToken();

        $pathReducer = $this->pathReducerManager->getPathReducer($bug->getTask()->getReducer());
        $pathReducer->reduce($bug);
    }
}

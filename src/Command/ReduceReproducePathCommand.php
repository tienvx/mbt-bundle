<?php

namespace Tienvx\Bundle\MbtBundle\Command;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Tienvx\Bundle\MbtBundle\Entity\ReproducePath;
use Tienvx\Bundle\MbtBundle\Service\PathReducerManager;

class ReduceReproducePathCommand extends Command
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
            ->setName('mbt:reduce-reproduce-path')
            ->setDescription('Reduce a reproduce path.')
            ->setHelp("Make reproduce path's steps shorter.")
            ->addArgument('reproduce-path-id', InputArgument::REQUIRED, 'The reproduce path id to reduce.');
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @throws \Exception
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $reproducePathId = $input->getArgument('reproduce-path-id');
        /** @var ReproducePath $reproducePath */
        $reproducePath = $this->entityManager->getRepository(ReproducePath::class)->find($reproducePathId);

        if (!$reproducePath) {
            $output->writeln(sprintf('No reproduce path found for id %d', $reproducePathId));
            return;
        }

        $pathReducer = $this->pathReducerManager->getPathReducer($reproducePath->getTask()->getReducer());
        $pathReducer->reduce($reproducePath);
    }
}

<?php

namespace Tienvx\Bundle\MbtBundle\Command;

use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Messenger\MessageBusInterface;
use Tienvx\Bundle\MbtBundle\Entity\Bug;
use Tienvx\Bundle\MbtBundle\PathReducer\PathReducerManager;

class ReduceBugCommand extends AbstractCommand
{
    /**
     * @var PathReducerManager
     */
    private $pathReducerManager;

    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     * @var MessageBusInterface
     */
    private $messageBus;

    public function __construct(
        PathReducerManager $pathReducerManager,
        EntityManagerInterface $entityManager,
        MessageBusInterface $messageBus
    ) {
        $this->pathReducerManager = $pathReducerManager;
        $this->entityManager = $entityManager;
        $this->messageBus = $messageBus;

        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->setName('mbt:bug:reduce')
            ->setDescription('Reduce the reproduce steps of the bug.')
            ->setHelp("Make bug's reproduce steps shorter.")
            ->addArgument('bug-id', InputArgument::REQUIRED, 'The bug id to reduce the steps.');
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
                $bug->setStatus('reducing');
            }

            return $bug;
        };

        $bug = $this->entityManager->transactional($callback);

        if (!$bug instanceof Bug) {
            $output->writeln(sprintf('No bug found for id %d', $bugId));

            return;
        }

        $this->setAnonymousToken();

        $pathReducer = $this->pathReducerManager->getPathReducer($bug->getTask()->getReducer());
        $pathReducer->reduce($bug);
    }
}

<?php

namespace Tienvx\Bundle\MbtBundle\Command;

use Exception;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Tienvx\Bundle\MbtBundle\PathReducer\PathReducerManager;

class ReducePathCommand extends AbstractCommand
{
    private $pathReducerManager;

    public function __construct(PathReducerManager $pathReducerManager)
    {
        $this->pathReducerManager = $pathReducerManager;

        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->setName('mbt:path:reduce')
            ->setDescription("Handle a path reducer's message.")
            ->setHelp('Call path reducer to handle a message that was come from itself')
            ->addArgument('bug-id', InputArgument::REQUIRED, 'The bug id.')
            ->addArgument('reducer', InputArgument::REQUIRED, 'The path reducer.')
            ->addArgument('length', InputArgument::REQUIRED, 'The path length.')
            ->addArgument('from', InputArgument::REQUIRED, 'From places.')
            ->addArgument('to', InputArgument::REQUIRED, 'To places.');
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @throws Exception
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $bugId = $input->getArgument('bug-id');
        $reducer = $input->getArgument('reducer');
        $length = $input->getArgument('length');
        $from = $input->getArgument('from');
        $to = $input->getArgument('to');

        $this->setAnonymousToken();

        $pathReducer = $this->pathReducerManager->getPathReducer($reducer);
        $pathReducer->handle($bugId, $length, $from, $to);
    }
}

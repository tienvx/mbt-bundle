<?php

namespace Tienvx\Bundle\MbtBundle\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Tienvx\Bundle\MbtBundle\PathReducer\PathReducerManager;

class HandlePathReducerMessageCommand extends Command
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
            ->setName('mbt:handle-path-reducer-message')
            ->setDescription("Handle a path reducer's message.")
            ->setHelp('Call path reducer to handle a message, the message was come from that path reducer')
            ->addArgument('reducer', InputArgument::REQUIRED, 'The path reducer.')
            ->addArgument('message', InputArgument::REQUIRED, 'The json encoded message.');
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @throws \Exception
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $reducer = $input->getArgument('reducer');
        $pathReducer = $this->pathReducerManager->getPathReducer($reducer);
        $pathReducer->handle($input->getArgument('message'));
    }
}

<?php

namespace Tienvx\Bundle\MbtBundle\Command;

use Exception;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Tienvx\Bundle\MbtBundle\Helper\Constants;
use Tienvx\Bundle\MbtBundle\Message\ReductionMessage;
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
            ->addArgument('reducer', InputArgument::OPTIONAL, 'The path reducer.', Constants::DEFAULT_REDUCER)
            ->addArgument('data', InputArgument::OPTIONAL, 'The json encoded data.', '[]');
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
        $data = json_decode($input->getArgument('data'), true);
        $message = new ReductionMessage($bugId, $reducer, $data);

        $this->setAnonymousToken();

        $pathReducer = $this->pathReducerManager->getPathReducer($reducer);
        $pathReducer->handle($message);
    }
}

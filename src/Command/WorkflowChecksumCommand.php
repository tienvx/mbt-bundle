<?php

namespace Tienvx\Bundle\MbtBundle\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Tienvx\Bundle\MbtBundle\Helper\WorkflowHelper;

class WorkflowChecksumCommand extends Command
{
    protected static $defaultName = 'mbt:workflow:checksum';

    /**
     * @var WorkflowHelper
     */
    private $workflowHelper;

    public function __construct(WorkflowHelper $workflowHelper)
    {
        $this->workflowHelper = $workflowHelper;

        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->setDescription('Print checksum of a workflow.')
            ->setHelp('Print md5 value of all places and transitions of the workflow.')
            ->addArgument('workflow-name', InputArgument::REQUIRED, 'The workflow to checksum.');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $workflowName = $input->getArgument('workflow-name');
        $checksum = $this->workflowHelper->checksum($workflowName);

        $output->writeln([
            sprintf('<info>Here is the checksum of workflow %s: %s</info>', $workflowName, $checksum),
        ]);

        return 0;
    }
}

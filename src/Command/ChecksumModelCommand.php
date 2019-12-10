<?php

namespace Tienvx\Bundle\MbtBundle\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Tienvx\Bundle\MbtBundle\Helper\ModelHelper;

class ChecksumModelCommand extends Command
{
    protected static $defaultName = 'mbt:model:checksum';

    /**
     * @var ModelHelper
     */
    private $modelHelper;

    public function __construct(ModelHelper $modelHelper)
    {
        $this->modelHelper = $modelHelper;

        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->setDescription('Print checksum of a model.')
            ->setHelp('Print md5 value of all places and transitions of the model.')
            ->addArgument('model', InputArgument::REQUIRED, 'The model to checksum.');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $model = $input->getArgument('model');
        $checksum = $this->modelHelper->checksum($model);

        $output->writeln([
            sprintf('<info>Here is the checksum of model %s: %s</info>', $model, $checksum),
        ]);

        return 0;
    }
}

<?php

namespace Tienvx\Bundle\MbtBundle\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Workflow\Dumper\GraphvizDumper;
use Tienvx\Bundle\MbtBundle\Exception\ModelNotFoundException;
use Tienvx\Bundle\MbtBundle\Model\Model;
use Tienvx\Bundle\MbtBundle\Service\ModelRegistry;

class DumpCommand extends Command
{
    private $modelRegistry;

    public function __construct(ModelRegistry $modelRegistry)
    {
        $this->modelRegistry = $modelRegistry;

        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->setName('mbt:dump')
            ->setDescription('Dump model into content of a dot file.')
            ->setHelp('This command dump model into content of a dot file, then you can use dot command to generate image file.')
            ->addArgument('model', InputArgument::REQUIRED, 'The model to dump.');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $modelArgument = $input->getArgument('model');
        $model = $this->modelRegistry->get($modelArgument);
        if (!$model instanceof Model) {
            $message = sprintf('Can not load model by id "%s".', $modelArgument);
            throw new ModelNotFoundException($message);
        }

        $dumper = new GraphvizDumper();
        $output->write($dumper->dump($model->getDefinition()));
    }
}

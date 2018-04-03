<?php

namespace Tienvx\Bundle\MbtBundle\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Tienvx\Bundle\MbtBundle\Model\Model;
use Tienvx\Bundle\MbtBundle\Service\GeneratorManager;
use Tienvx\Bundle\MbtBundle\Service\ModelRegistry;

class GenerateCommand extends Command
{
    private $modelRegistry;
    private $generatorManager;

    public function __construct(ModelRegistry $modelRegistry, GeneratorManager $generatorManager)
    {
        $this->modelRegistry = $modelRegistry;
        $this->generatorManager = $generatorManager;

        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->setName('mbt:generate')
            ->setDescription('Generate test sequence from a model using a specific generator.')
            ->setHelp('This command allows you to generate test sequence without actually testing the system.')
            ->addArgument('model', InputArgument::REQUIRED, 'The model to generate.')
            ->addOption('generator', 'g', InputOption::VALUE_OPTIONAL, 'The way to generate test sequence from model.', 'random')
            ->addOption('arguments', 'a', InputOption::VALUE_OPTIONAL, 'The arguments pass to generator.', '{"stop":{"on":"coverage","at":{"edgeCoverage":100,"vertexCoverage":100}}}');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $generator = $this->generatorManager->getGenerator($input->getOption('generator'));
        $model = $this->modelRegistry->get($input->getArgument('model'));

        $arguments = $input->getOption('arguments');
        if (is_string($arguments)) {
            $arguments = json_decode($arguments, true);
        }
        else {
            $arguments = [];
        }

        $generator->init($model, $arguments);

        while (!$generator->meetStopCondition() && $edge = $generator->getNextStep()) {
            if ($generator->canGoNextStep($edge)) {
                $generator->goToNextStep($edge);
            }
        }

        $path = $generator->getPath();
        $output->writeln((string) $path);
    }
}

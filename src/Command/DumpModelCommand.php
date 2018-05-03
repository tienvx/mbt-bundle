<?php

namespace Tienvx\Bundle\MbtBundle\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Workflow\Dumper\PlantUmlDumper;
use Symfony\Component\Workflow\Dumper\StateMachineGraphvizDumper;
use Symfony\Component\Workflow\Registry;
use Tienvx\Bundle\MbtBundle\Service\ModelRegistry;

class DumpModelCommand extends Command
{
    private $modelRegistry;
    private $workflows;

    public function __construct(ModelRegistry $modelRegistry, Registry $workflows)
    {
        $this->modelRegistry = $modelRegistry;
        $this->workflows = $workflows;

        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->setName('mbt:dump-model')
            ->setDescription('Dump model into dot file\'s content.')
            ->setHelp('Dump model into dot file\'s content. Then ot can be passed to dot command to generate image file.')
            ->addArgument('model', InputArgument::REQUIRED, 'The model to dump.')
            ->addOption('format', 'f', InputOption::VALUE_OPTIONAL, 'The dump format [dot|puml]', 'dot');
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @throws \Exception
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $model = $input->getArgument('model');
        $workflowMetadata = $this->modelRegistry->getModel($model);
        $subject = $workflowMetadata['subject'];
        $subject = new $subject();
        $workflow = $this->workflows->get($subject, $model);

        if ('puml' === $input->getOption('format')) {
            $dumper = new PlantUmlDumper(PlantUmlDumper::STATEMACHINE_TRANSITION);
        } else {
            $dumper = new StateMachineGraphvizDumper();
        }
        $output->write($dumper->dump($workflow->getDefinition()));
    }
}

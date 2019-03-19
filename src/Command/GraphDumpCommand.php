<?php

namespace Tienvx\Bundle\MbtBundle\Command;

use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Exception\InvalidArgumentException;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Workflow\Workflow;
use Tienvx\Bundle\MbtBundle\Graph\Dumper\GraphvizDumper;
use Tienvx\Bundle\MbtBundle\Graph\Dumper\PlantUmlDumper;
use Tienvx\Bundle\MbtBundle\Helper\VertexHelper;
use Tienvx\Bundle\MbtBundle\Service\GraphBuilder;

class GraphDumpCommand extends Command
{
    protected static $defaultName = 'mbt:graph:dump';

    /**
     * @var GraphBuilder
     */
    protected $graphBuilder;

    public function __construct(GraphBuilder $graphBuilder)
    {
        parent::__construct();

        $this->graphBuilder = $graphBuilder;
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setDefinition(array(
                new InputArgument('name', InputArgument::REQUIRED, 'A workflow name'),
                new InputOption('label', 'l', InputOption::VALUE_REQUIRED, 'Labels a graph'),
                new InputOption('dump-format', null, InputOption::VALUE_REQUIRED, 'The dump format [dot|puml]', 'dot'),
            ))
            ->setDescription('Dump a graph')
            ->setHelp(
                <<<'EOF'
The <info>%command.name%</info> command dumps the graphical representation of a
graph (transfered from a workflow) in different formats

<info>DOT</info>:  %command.full_name% <workflow name> | dot -Tpng > workflow.png
<info>PUML</info>: %command.full_name% <workflow name> --dump-format=puml | java -jar plantuml.jar -p > workflow.png

EOF
            )
        ;
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        /** @var Application $application */
        $application = $this->getApplication();
        $container = $application->getKernel()->getContainer();
        $serviceId = $input->getArgument('name');

        /** @var Workflow $workflow */
        if ($container->has('workflow.'.$serviceId)) {
            $workflow = $container->get('workflow.'.$serviceId);
        } elseif ($container->has('state_machine.'.$serviceId)) {
            $workflow = $container->get('state_machine.'.$serviceId);
        } else {
            throw new InvalidArgumentException(sprintf('No service found for "workflow.%1$s" nor "state_machine.%1$s".', $serviceId));
        }

        $graph = $this->graphBuilder->build($workflow);

        if ('puml' === $input->getOption('dump-format')) {
            $dumper = new PlantUmlDumper();
        } else {
            $dumper = new GraphvizDumper();
        }

        $options = array(
            'name' => $serviceId,
            'nofooter' => true,
            'graph' => array(
                'label' => $input->getOption('label'),
            ),
        );
        $output->writeln($dumper->dump(VertexHelper::getId([$workflow->getDefinition()->getInitialPlace()]), $graph, $options));
    }
}

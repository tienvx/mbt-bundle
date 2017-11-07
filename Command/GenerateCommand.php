<?php

namespace Tienvx\Bundle\MbtBundle\Command;

use Fhaculty\Graph\Edge\Directed;
use Fhaculty\Graph\Vertex;
use Fhaculty\Graph\Walk;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Tienvx\Bundle\MbtBundle\Exception\ModelNotFoundException;
use Tienvx\Bundle\MbtBundle\Model\Model;
use Tienvx\Bundle\MbtBundle\Service\TraversalFactory;

class GenerateCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('mbt:generate')
            ->setDescription('Generate test sequence from a model using a specific traversal.')
            ->setHelp('This command allows you to generate test sequence without actually testing the system.')
            ->addArgument('model', InputArgument::REQUIRED, 'The model to generate.')
            ->addOption('traversal', 't', InputOption::VALUE_OPTIONAL, 'The way to traverse through model to generate test sequence.', 'random(100,100)');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $modelArgument = $input->getArgument('model');
        $model = $this->getContainer()->get("model.{$modelArgument}");
        if (!$model instanceof Model) {
            $message = sprintf('Can not load model by id "%s".', $modelArgument);
            throw new ModelNotFoundException($message);
        }

        $traversalOption = $input->getOption('traversal');
        /** @var $factory TraversalFactory */
        $factory = $this->getContainer()->get('tienvx_mbt.traversal_factory');
        $traversal = $factory->get($this->getContainer(), $traversalOption, $model);

        while (!$traversal->meetStopCondition() && $edge = $traversal->getNextStep()) {
            if ($traversal->canGoNextStep($edge)) {
                $traversal->goToNextStep($edge);
            }
        }

        $sequence = [];
        $walk = Walk::factoryFromEdges($traversal->getEdges(), $traversal->getStartVertex());
        $steps = $walk->getAlternatingSequence();
        foreach ($steps as $step) {
            if ($step instanceof Vertex) {
                $sequence[] = $step->getAttribute('name');
            }
            else if ($step instanceof Directed) {
                $data = $step->getAttribute('data');
                array_walk($data, function (&$value, $key) {
                    $value = "$key=$value";
                });
                $sequence[] = $step->getAttribute('name') . '(' . implode(',', $data) . ')';
            }
        }
        $output->writeln(implode(' ', $sequence));
    }
}

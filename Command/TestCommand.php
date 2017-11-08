<?php

namespace Tienvx\Bundle\MbtBundle\Command;

use Fhaculty\Graph\Edge\Directed;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Tienvx\Bundle\MbtBundle\Exception\ModelNotFoundException;
use Tienvx\Bundle\MbtBundle\Graph\Path;
use Tienvx\Bundle\MbtBundle\Model\Model;
use Tienvx\Bundle\MbtBundle\Service\PathReducer;
use Tienvx\Bundle\MbtBundle\Service\TraversalFactory;

class TestCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('mbt:test')
            ->setDescription('Test system defined by a model using a specific traversal then report bug if found.')
            ->setHelp('This command test the system step by step defined by a model using a specific traversal, then report bug if found.')
            ->addArgument('model', InputArgument::REQUIRED, 'The model to test.')
            ->addOption('traversal', 't', InputOption::VALUE_OPTIONAL, 'The way to traverse through model to generate test sequence to test.', 'random(100,100)');
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
        /** @var TraversalFactory $factory */
        $factory = $this->getContainer()->get('tienvx_mbt.traversal_factory');
        $traversal = $factory->get($this->getContainer(), $traversalOption, $model);

        try {
            while (!$traversal->meetStopCondition() && $edge = $traversal->getNextStep()) {
                if ($traversal->canGoNextStep($edge)) {
                    $traversal->goToNextStep($edge, true);
                }
            }
        }
        catch (\Throwable $throwable) {
            /** @var $reducer PathReducer */
            $reducer = $this->getContainer()->get('tienvx_mbt.path_reducer');
            $path = Path::factoryFromEdges($traversal->getEdges(), $traversal->getStartVertex());
            $path = $reducer->reduce($path, $model, $throwable);

            $output->writeln('Found a bug: ' . $throwable->getMessage());

            $output->writeln('Steps to reproduce:');
            $table = new Table($output);
            $table->setHeaders(array('Step', 'Label', 'Data'));
            /** @var $edge Directed */
            foreach ($path->getEdges() as $index => $edge) {
                $table->addRow([$index + 1, $edge->getAttribute('label'), json_encode($edge->getAttribute('data'))]);
            }
            $table->render();
        }
    }
}

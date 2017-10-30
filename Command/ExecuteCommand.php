<?php

namespace Tienvx\Bundle\MbtBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Tienvx\Bundle\MbtBundle\Exception\ModelNotFoundException;
use Tienvx\Bundle\MbtBundle\Exception\TransitionCanNotBeAppliedException;
use Tienvx\Bundle\MbtBundle\Model\Model;
use Tienvx\Bundle\MbtBundle\Subject\Subject;
use Tienvx\Bundle\MbtBundle\Exception\ModelNotInPlaceException;

class ExecuteCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('mbt:execute')
            ->setDescription('Execute test sequence generated from mbt:generate command.')
            ->setHelp('This command allows you to execute test sequence that is generated from mbt:generate command.')
            ->addArgument('model', InputArgument::REQUIRED, 'The model to execute.')
            ->addArgument('test-sequence', InputArgument::REQUIRED, 'The test squence to execute.');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $modelArgument = $input->getArgument('model');
        $model = $this->getContainer()->get("model.{$modelArgument}");
        if (!$model instanceof Model) {
            $message = sprintf('Can not load model by id "%s".', $modelArgument);
            throw new ModelNotFoundException($message);
        }

        $testSequence = $input->getArgument('test-sequence');

        $progress = new ProgressBar($output);
        $progress->setMessage(sprintf('Executing test sequence for model "%s"', $modelArgument));
        $progress->start(100);

        $subjectClass = $model->getSubject();
        /* @var $subject Subject */
        $subject = new $subjectClass();

        $subject->setCallSUT(true);

        $message = 'No bug found';

        $steps = explode(' ', $testSequence);
        foreach ($steps as $index => $step) {
            $marking = $model->getMarking($subject);
            $pos = strpos($step, '(');
            if ($pos === false) {
                $place = $step;
                if (!$marking->has($place)) {
                    throw new ModelNotInPlaceException(sprintf('Model "%s" can not be at place "%s"', $modelArgument, $place));
                }
            }
            else if (preg_match('/([a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*)\((.*)\)/', $step, $matches)) {
                $transition = $matches[1];
                $data = [];
                if ($matches[2]) {
                    $params = explode(',', $matches[2]);
                    foreach ($params as $param) {
                        list($key, $value) = explode('=', $param);
                        $data[$key] = $value;
                    }
                }
                if ($model->can($subject, $transition)) {
                    $transitionObject = $model->getTransition($transition);
                    $transitionObject->setData($data);
                    try {
                        $model->apply($subject, $transition);
                    }
                    catch (\Throwable $throwable) {
                        $message = 'Found a bug: ' . $throwable;
                        break;
                    }
                }
                else {
                    throw new TransitionCanNotBeAppliedException(sprintf('Model "%s" can apply transition "%s"', $modelArgument, $transition));
                }
            }
            $progress->setProgress((int) floor(($index + 1) / count($steps) * 100));
        }
        $progress->finish();

        $output->writeln([
            '===Begin execution results===',
            $message,
            '===End execution results==='
        ]);
    }
}

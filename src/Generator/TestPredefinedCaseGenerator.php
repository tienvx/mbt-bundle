<?php

namespace Tienvx\Bundle\MbtBundle\Generator;

use Exception;
use Generator;
use Symfony\Component\Workflow\Workflow;
use Tienvx\Bundle\MbtBundle\Entity\GeneratorOptions;
use Tienvx\Bundle\MbtBundle\Helper\WorkflowHelper;
use Tienvx\Bundle\MbtBundle\PredefinedCase\PredefinedCaseManager;
use Tienvx\Bundle\MbtBundle\Subject\AbstractSubject;

class TestPredefinedCaseGenerator extends AbstractGenerator
{
    /**
     * @var PredefinedCaseManager
     */
    private $predefinedCaseManager;

    public function __construct(PredefinedCaseManager $predefinedCaseManager)
    {
        $this->predefinedCaseManager = $predefinedCaseManager;
    }

    /**
     * {@inheritdoc}
     *
     * @throws Exception
     */
    public function generate(Workflow $workflow, AbstractSubject $subject, GeneratorOptions $generatorOptions = null): Generator
    {
        $name = $generatorOptions->getPredefinedCase();

        if (!$name) {
            throw new Exception('Missing pre-defined case name');
        }

        if (!$this->predefinedCaseManager->has($name)) {
            throw new Exception(sprintf('No pre-defined case found for name %s', $name));
        }

        $predefinedCase = $this->predefinedCaseManager->get($name);
        if ($predefinedCase->getModel()->getName() !== $workflow->getName()) {
            throw new Exception(sprintf('The pre-defined case with name %s can not be tested with workflow %s', $name, $workflow->getName()));
        }

        if (!WorkflowHelper::validate($predefinedCase, $workflow)) {
            throw new Exception(sprintf('The pre-defined case with name %s is outdated with workflow %s', $name, $workflow->getName()));
        }

        foreach ($predefinedCase->getSteps() as $step) {
            yield $step;
        }
    }

    public static function getName(): string
    {
        return 'test-predefined-case';
    }

    public function getLabel(): string
    {
        return 'Test Pre-defined Case';
    }
}

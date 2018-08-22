<?php

namespace Tienvx\Bundle\MbtBundle\Reporter;

use Symfony\Component\Workflow\Registry;
use Tienvx\Bundle\MbtBundle\Entity\Bug;
use Tienvx\Bundle\MbtBundle\Graph\Path;
use Tienvx\Bundle\MbtBundle\Helper\GraphBuilder;
use Tienvx\Bundle\MbtBundle\Subject\SubjectManager;

abstract class AbstractReporter implements ReporterInterface
{
    /**
     * @var Registry
     */
    protected $workflowRegistry;

    /**
     * @var SubjectManager
     */
    protected $subjectManager;

    public function __construct(
        Registry $workflowRegistry,
        SubjectManager $subjectManager
    ) {
        $this->workflowRegistry = $workflowRegistry;
        $this->subjectManager = $subjectManager;
    }

    /**
     * @param Bug $bug
     * @return array
     * @throws \Exception
     */
    protected function buildSteps(Bug $bug): array
    {
        $model = $bug->getTask()->getModel();
        $subject = $this->subjectManager->createSubjectForModel($model);
        $workflow = $this->workflowRegistry->get($subject, $model);
        $graph = GraphBuilder::build($workflow);
        $path = Path::fromSteps($bug->getSteps(), $graph);

        $steps = [];
        foreach ($path->getTransitions() as $index => $edge) {
            $steps[] = [
                'step' => $index + 1,
                'action' => $edge->getAttribute('label'),
                'data' => json_encode($path->getDataAt($index) ?? []),
            ];
        }
        return $steps;
    }
}

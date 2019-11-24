<?php

namespace Tienvx\Bundle\MbtBundle\Workflow;

use Symfony\Component\Workflow\DefinitionBuilder;
use Symfony\Component\Workflow\MarkingStore\MethodMarkingStore;
use Symfony\Component\Workflow\Transition;
use Symfony\Component\Workflow\Workflow;
use Tienvx\Bundle\MbtBundle\Entity\Task;

class TaskWorkflow
{
    // Places
    public const NOT_STARTED = 'not-started';
    public const IN_PROGRESS = 'in-progress';
    public const COMPLETED = 'completed';

    // Transitions
    public const START = 'start';
    public const COMPLETE = 'complete';

    /**
     * @var Workflow
     */
    protected $workflow;

    public function __construct()
    {
        $definitionBuilder = new DefinitionBuilder();
        $definition = $definitionBuilder->addPlaces([self::NOT_STARTED, self::IN_PROGRESS, self::COMPLETED])
            ->addTransition(new Transition(self::START, self::NOT_STARTED, self::IN_PROGRESS))
            ->addTransition(new Transition(self::COMPLETE, self::IN_PROGRESS, self::COMPLETED))
            ->build()
        ;

        $singleState = true;
        $property = 'status';
        $marking = new MethodMarkingStore($singleState, $property);
        $this->workflow = new Workflow($definition, $marking, null, 'task-workflow');
    }

    public function apply(Task $task, string $transition): void
    {
        $this->workflow->apply($task, $transition);
    }
}

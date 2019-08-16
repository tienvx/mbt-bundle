<?php

namespace Tienvx\Bundle\MbtBundle\Workflow;

use Symfony\Component\Workflow\DefinitionBuilder;
use Symfony\Component\Workflow\MarkingStore\MethodMarkingStore;
use Symfony\Component\Workflow\Transition;
use Symfony\Component\Workflow\Workflow;
use Tienvx\Bundle\MbtBundle\Entity\Bug;

class BugWorkflow
{
    // Places
    const NEW = 'new';
    const REDUCING = 'reducing';
    const REDUCED = 'reduced';
    //const REPORTING = 'reporting';
    //const REPORTED = 'reported';
    //const CAPTURING = 'capturing';
    //const CAPTURED = 'reported';

    // Transitions
    const REDUCE = 'reduce';
    const COMPLETE_REDUCE = 'complete_reduce';
    //const REPORT = 'report';
    //const COMPLETE_REPORT = 'complete_report';
    //const CAPTURE = 'report';
    //const COMPLETE_CAPTURE = 'complete_report';

    /**
     * @var Workflow
     */
    protected $workflow;

    public function __construct()
    {
        $definitionBuilder = new DefinitionBuilder();
        $definition = $definitionBuilder->addPlaces([self::NEW, self::REDUCING, self::REDUCED/*, self::REPORTING, self::REPORTED, self::CAPTURING, self::CAPTURED*/])
            ->addTransition(new Transition(self::REDUCE, self::NEW, self::REDUCING))
            ->addTransition(new Transition(self::COMPLETE_REDUCE, self::REDUCING, self::REDUCED))
            //->addTransition(new Transition(self::REPORT, self::REDUCED, self::REPORTING))
            //->addTransition(new Transition(self::COMPLETE_REPORT, self::REPORTING, self::REPORTED))
            //->addTransition(new Transition(self::CAPTURE, self::REPORTED, self::CAPTURING))
            //->addTransition(new Transition(self::COMPLETE_CAPTURE, self::CAPTURING, self::CAPTURED))
            ->build()
        ;

        $singleState = true;
        $property = 'status';
        $marking = new MethodMarkingStore($singleState, $property);
        $this->workflow = new Workflow($definition, $marking, null, 'bug-workflow');
    }

    public function apply(Bug $bug, string $transition)
    {
        $this->workflow->apply($bug, $transition);
    }
}

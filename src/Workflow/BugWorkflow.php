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
    public const NEW = 'new';
    public const REDUCING = 'reducing';
    public const REDUCED = 'reduced';
    public const CLOSED = 'closed';

    // Transitions
    public const REDUCE = 'reduce';
    public const COMPLETE_REDUCE = 'complete_reduce';
    public const REDUCE_AGAIN = 'reduce_again';
    public const CLOSE = 'close';
    public const REOPEN = 'reopen';

    /**
     * @var Workflow
     */
    protected $workflow;

    public function __construct()
    {
        $definitionBuilder = new DefinitionBuilder();
        $definition = $definitionBuilder->addPlaces([self::NEW, self::REDUCING, self::REDUCED, self::CLOSED])
            ->addTransition(new Transition(self::REDUCE, self::NEW, self::REDUCING))
            ->addTransition(new Transition(self::COMPLETE_REDUCE, self::REDUCING, self::REDUCED))
            ->addTransition(new Transition(self::CLOSE, self::REDUCED, self::CLOSED))
            ->addTransition(new Transition(self::REOPEN, self::CLOSED, self::REDUCED))
            ->addTransition(new Transition(self::REDUCE_AGAIN, self::REDUCED, self::REDUCING))
            ->build()
        ;

        $singleState = true;
        $property = 'status';
        $marking = new MethodMarkingStore($singleState, $property);
        $this->workflow = new Workflow($definition, $marking, null, 'bug-workflow');
    }

    public function apply(Bug $bug, string $transition): void
    {
        $this->workflow->apply($bug, $transition);
    }
}

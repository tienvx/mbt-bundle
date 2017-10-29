<?php

namespace Tienvx\Bundle\MbtBundle\Model;

use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Workflow\Definition;
use Symfony\Component\Workflow\MarkingStore\SingleStateMarkingStore;
use Symfony\Component\Workflow\StateMachine;
use Tienvx\Bundle\MbtBundle\Subject\Subject;

class Model extends StateMachine
{
    /**
     * @var Subject
     */
    protected $subject;

    public function __construct(Definition $definition, string $subject, EventDispatcherInterface $dispatcher = null, $name = 'unnamed')
    {
        $this->subject = $subject;
        parent::__construct($definition, new SingleStateMarkingStore(), $dispatcher, $name);
    }

    public function getSubject()
    {
        return $this->subject;
    }

    /**
     * @param string $transitionName
     * @return null|Transition
     */
    public function getTransition($transitionName)
    {
        foreach ($this->getDefinition()->getTransitions() as $transition) {
            if ($transitionName === $transition->getName()) {
                return $transition;
            }
        }

        return null;
    }
}

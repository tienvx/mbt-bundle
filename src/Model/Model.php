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

    /**
     * @var string
     */
    protected $label;

    public function __construct(Definition $definition, string $subject, EventDispatcherInterface $dispatcher = null, $name = 'unnamed', $label = '')
    {
        $this->subject = $subject;
        $this->label = $label;
        parent::__construct($definition, new SingleStateMarkingStore(), $dispatcher, $name);
    }

    public function getSubject()
    {
        return $this->subject;
    }

    public function getLabel()
    {
        return $this->label;
    }
}

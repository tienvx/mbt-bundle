<?php

namespace Tienvx\Bundle\MbtBundle\Model;

use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Workflow\Definition;
use Symfony\Component\Workflow\MarkingStore\SingleStateMarkingStore;
use Symfony\Component\Workflow\StateMachine;

class Model extends StateMachine
{
    private $subject;

    public function __construct(Definition $definition, string $subject, EventDispatcherInterface $dispatcher = null, $name = 'unnamed')
    {
        $this->subject = $subject;
        parent::__construct($definition, new SingleStateMarkingStore(), $dispatcher, $name);
    }

    public function getSubject()
    {
        return $this->subject;
    }
}

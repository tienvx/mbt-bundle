<?php

namespace Tienvx\Bundle\MbtBundle\Model;

use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Workflow\Definition;
use Symfony\Component\Workflow\MarkingStore\SingleStateMarkingStore;
use Symfony\Component\Workflow\StateMachine;

class Model extends StateMachine
{
    private $instance;

    public function __construct(Definition $definition, string $instance, EventDispatcherInterface $dispatcher = null, $name = 'unnamed')
    {
        $this->instance = $instance;
        parent::__construct($definition, new SingleStateMarkingStore(), $dispatcher, $name);
    }

    public function getInstance()
    {
        return $this->instance;
    }
}

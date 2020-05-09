<?php

namespace Tienvx\Bundle\MbtBundle\Subject;

use Exception;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;
use Tienvx\Bundle\MbtBundle\Event\SubjectInitEvent;
use Tienvx\Bundle\MbtBundle\Model\SubjectInterface;

class SubjectManager
{
    /**
     * @var array
     */
    protected $subjects;

    /**
     * @var EventDispatcherInterface
     */
    protected $dispatcher;

    public function __construct(array $subjects, EventDispatcherInterface $dispatcher)
    {
        $this->subjects = $subjects;
        $this->dispatcher = $dispatcher;
    }

    public function create(string $workflowName, bool $trying = false): SubjectInterface
    {
        $class = $this->subjects[$workflowName] ?? null;
        if (is_null($class)) {
            throw new Exception(sprintf('Subject for workflow %s not found', $workflowName));
        }

        $subject = new $class();
        $event = new SubjectInitEvent($subject, $trying);
        $this->dispatcher->dispatch($event, SubjectInitEvent::NAME);

        return $subject;
    }
}

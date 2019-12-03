<?php

namespace Tienvx\Bundle\MbtBundle\Helper;

use Tienvx\Bundle\MbtBundle\Steps\Data;
use Tienvx\Bundle\MbtBundle\Subject\SubjectInterface;

class SubjectHelper
{
    /**
     * @var array
     */
    protected $places;

    /**
     * @var array
     */
    protected $transitions;

    public function __construct(array $places, array $transitions)
    {
        $this->places = $places;
        $this->transitions = $transitions;
    }

    public function invokePlace(SubjectInterface $subject, string $place): void
    {
        $subjectClass = get_class($subject);

        if (isset($this->places[$subjectClass][$place])) {
            $method = $this->places[$subjectClass][$place];
            $callable = [$subject, $method];
            if (is_callable($callable)) {
                $callable();
            }
        }
    }

    public function invokeTransition(SubjectInterface $subject, string $transition, ?Data $data): void
    {
        $subjectClass = get_class($subject);

        if (isset($this->transitions[$subjectClass][$transition])) {
            $method = $this->transitions[$subjectClass][$transition];
            $callable = [$subject, $method];
            if (is_callable($callable) && $data instanceof Data) {
                $callable($data);
            }
        }
    }
}

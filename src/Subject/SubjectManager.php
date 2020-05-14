<?php

namespace Tienvx\Bundle\MbtBundle\Subject;

use Exception;
use Tienvx\Bundle\MbtBundle\Model\Subject\SetUpInterface;
use Tienvx\Bundle\MbtBundle\Model\SubjectInterface;

class SubjectManager
{
    /**
     * @var array
     */
    protected $subjects;

    public function __construct(array $subjects)
    {
        $this->subjects = $subjects;
    }

    public function create(string $workflowName, bool $trying = false): SubjectInterface
    {
        $class = $this->subjects[$workflowName] ?? null;
        if (is_null($class)) {
            throw new Exception(sprintf('Subject for workflow %s not found', $workflowName));
        }

        $subject = new $class();
        if ($subject instanceof SetUpInterface) {
            $subject->setUp($trying);
        }

        return $subject;
    }
}

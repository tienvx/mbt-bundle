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

    /**
     * @throws Exception
     */
    public function create(string $workflowName): SubjectInterface
    {
        $class = $this->subjects[$workflowName] ?? null;
        if (!is_null($class)) {
            return new $class();
        }
        throw new Exception(sprintf('Subject for workflow %s not found', $workflowName));
    }

    public function createAndSetUp(string $workflowName, bool $trying = false): SubjectInterface
    {
        $subject = $this->create($workflowName);
        if ($subject instanceof SetUpInterface) {
            $subject->setUp($trying);
        }

        return $subject;
    }
}

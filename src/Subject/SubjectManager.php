<?php

namespace Tienvx\Bundle\MbtBundle\Subject;

use Exception;

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
    public function create(string $model): SubjectInterface
    {
        $class = $this->subjects[$model] ?? null;
        if (!is_null($class)) {
            return new $class();
        }
        throw new Exception(sprintf('Subject for model %s not found', $model));
    }

    public function createAndSetUp(string $model, bool $testing = false): SubjectInterface
    {
        $subject = $this->create($model);
        $subject->setUp($testing);

        return $subject;
    }
}

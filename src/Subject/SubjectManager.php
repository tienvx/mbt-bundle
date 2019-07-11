<?php

namespace Tienvx\Bundle\MbtBundle\Subject;

use Exception;

class SubjectManager
{
    /**
     * @var string[]
     */
    protected $subjects;

    public function __construct(array $subjects = [])
    {
        $this->subjects = $subjects;
    }

    public function hasSubject(string $model)
    {
        return isset($this->subjects[$model]);
    }

    public function getSubject(string $model)
    {
        return $this->subjects[$model];
    }

    /**
     * @param string $model
     *
     * @return AbstractSubject
     *
     * @throws Exception
     */
    public function createSubject(string $model): AbstractSubject
    {
        if (!isset($this->subjects[$model])) {
            throw new Exception(sprintf('Subject for model "%s" is not specified.', $model));
        } elseif (!class_exists($this->subjects[$model])) {
            throw new Exception(sprintf('Subject class for model "%s" does not exist.', $model));
        }

        $subject = new $this->subjects[$model]();
        if (!$subject instanceof AbstractSubject) {
            throw new Exception(sprintf('Subject for model "%s" is not instance of "%s".', $model, AbstractSubject::class));
        }

        return $subject;
    }
}

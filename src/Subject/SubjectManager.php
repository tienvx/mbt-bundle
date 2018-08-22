<?php

namespace Tienvx\Bundle\MbtBundle\Subject;

class SubjectManager
{
    /**
     * @var string[]
     */
    protected $subjects;

    public function __construct()
    {
        $this->subjects = [];
    }

    public function addSubject(string $model, string $subject)
    {
        if (class_exists($subject)) {
            $this->subjects[$model] = $subject;
        }
    }

    public function addSubjects(array $subjects)
    {
        foreach ($subjects as $model => $subject) {
            $this->addSubject($model, $subject);
        }
    }

    public function hasSubjectForModel(string $model)
    {
        return isset($this->subjects[$model]);
    }

    /**
     * @param string $model
     * @return Subject
     * @throws \Exception
     */
    public function createSubjectForModel(string $model): Subject
    {
        if (!isset($this->subjects[$model])) {
            throw new \Exception(sprintf('Subject for model %s is not specified.', $model));
        } elseif (!class_exists($this->subjects[$model])) {
            throw new \Exception(sprintf('Subject class for model %s does not exist.', $model));
        }

        $subject = new $this->subjects[$model];
        if (!$subject instanceof Subject) {
            throw new \Exception(sprintf('Subject for model %s is not instance of %s.', $model, Subject::class));
        }
        return $subject;
    }
}

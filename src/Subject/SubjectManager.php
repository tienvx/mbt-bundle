<?php

namespace Tienvx\Bundle\MbtBundle\Subject;

use Doctrine\Common\Annotations\Reader;
use Exception;
use ReflectionClass;
use Tienvx\Bundle\MbtBundle\Annotation\Subject;

class SubjectManager
{
    /**
     * @var string[] Subject classes
     */
    protected $subjects;

    /**
     * @var Reader
     */
    protected $reader;

    public function __construct(Reader $reader)
    {
        $this->reader = $reader;
    }

    public function setSubjects(array $subjects)
    {
        $this->subjects = $subjects;
    }

    /**
     * @throws Exception
     */
    public function create(string $model): SubjectInterface
    {
        foreach ($this->subjects as $subject) {
            $reflectionClass = new ReflectionClass($subject);
            $annotation = $this->reader->getClassAnnotation($reflectionClass, Subject::class);
            if ($annotation instanceof Subject && $annotation->getName() === $model) {
                return new $subject();
            }
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

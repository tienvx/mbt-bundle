<?php

namespace Tienvx\Bundle\MbtBundle\Subject;

use Doctrine\Common\Annotations\Reader;
use Exception;
use ReflectionClass;
use Tienvx\Bundle\MbtBundle\Annotation\Subject;

class SubjectManager
{
    /**
     * @var array
     */
    protected $classes = [];

    /**
     * @var Reader
     */
    protected $reader;

    public function __construct(Reader $reader, iterable $subjects)
    {
        $this->reader = $reader;
        $this->initSubjects($subjects);
    }

    public function initSubjects(iterable $subjects): void
    {
        foreach ($subjects as $subject) {
            $class = get_class($subject);
            $reflectionClass = new ReflectionClass($class);
            $annotation = $this->reader->getClassAnnotation($reflectionClass, Subject::class);
            if ($annotation instanceof Subject) {
                $model = $annotation->getName();
                $this->classes[$model] = $class;
            }
        }
    }

    /**
     * @throws Exception
     */
    public function create(string $model): SubjectInterface
    {
        $class = $this->classes[$model] ?? null;
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

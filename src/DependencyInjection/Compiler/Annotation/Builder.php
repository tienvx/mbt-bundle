<?php

namespace Tienvx\Bundle\MbtBundle\DependencyInjection\Compiler\Annotation;

use Doctrine\Common\Annotations\Reader;
use ReflectionClass;
use ReflectionMethod;
use Tienvx\Bundle\MbtBundle\Annotation\Place;
use Tienvx\Bundle\MbtBundle\Annotation\Subject;
use Tienvx\Bundle\MbtBundle\Annotation\Transition;

class Builder
{
    /**
     * @var Reader
     */
    private $reader;

    public function __construct(Reader $reader)
    {
        $this->reader = $reader;
    }

    public function build(string $subjectClass, array &$subjects, array &$places, array &$transitions): void
    {
        $reflectionClass = new ReflectionClass($subjectClass);

        $this->buildSubject($reflectionClass, $subjects);

        foreach ($reflectionClass->getMethods() as $reflectionMethod) {
            $this->buildPlace($reflectionClass, $reflectionMethod, $places);
            $this->buildTransition($reflectionClass, $reflectionMethod, $transitions);
        }
    }

    protected function buildSubject(ReflectionClass $reflectionClass, array &$subjects): void
    {
        $annotation = $this->reader->getClassAnnotation($reflectionClass, Subject::class);
        if ($annotation instanceof Subject) {
            $subjects[$annotation->getName()] = $reflectionClass->getName();
        }
    }

    protected function buildPlace(ReflectionClass $reflectionClass, ReflectionMethod $reflectionMethod, array &$places): void
    {
        $annotation = $this->reader->getMethodAnnotation($reflectionMethod, Place::class);
        if ($annotation instanceof Place) {
            $places[$reflectionClass->getName()][$annotation->getName()] = $reflectionMethod->getName();
        }
    }

    protected function buildTransition(ReflectionClass $reflectionClass, ReflectionMethod $reflectionMethod, array &$transitions): void
    {
        $annotation = $this->reader->getMethodAnnotation($reflectionMethod, Transition::class);
        if ($annotation instanceof Transition) {
            $transitions[$reflectionClass->getName()][$annotation->getName()] = $reflectionMethod->getName();
        }
    }
}

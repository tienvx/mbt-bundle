<?php

namespace Tienvx\Bundle\MbtBundle\DependencyInjection\Compiler;

use Doctrine\Common\Annotations\Reader;
use ReflectionClass;
use ReflectionMethod;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Tienvx\Bundle\MbtBundle\Annotation\Place;
use Tienvx\Bundle\MbtBundle\Annotation\Subject;
use Tienvx\Bundle\MbtBundle\Annotation\Transition;
use Tienvx\Bundle\MbtBundle\Helper\SubjectHelper;
use Tienvx\Bundle\MbtBundle\Subject\SubjectManager;

class SubjectPass implements CompilerPassInterface
{
    /**
     * @var string
     */
    private $subjectTag;

    public function __construct(string $subjectTag = 'mbt.subject')
    {
        $this->subjectTag = $subjectTag;
    }

    public function process(ContainerBuilder $container): void
    {
        $subjects = [];
        $places = [];
        $transitions = [];
        $reader = $container->get(Reader::class);
        foreach ($container->findTaggedServiceIds($this->subjectTag, true) as $serviceId => $attributes) {
            $definition = $container->getDefinition($serviceId);
            $subjectClass = $definition->getClass();
            $reflectionClass = new ReflectionClass($subjectClass);

            $this->buildSubject($reader, $reflectionClass, $subjects);

            foreach ($reflectionClass->getMethods() as $reflectionMethod) {
                $this->buildPlace($reader, $reflectionClass, $reflectionMethod, $places);
                $this->buildTransition($reader, $reflectionClass, $reflectionMethod, $transitions);
            }
        }

        $this->bindings($container, $subjects, $places, $transitions);
    }

    protected function buildSubject(Reader $reader, ReflectionClass $reflectionClass, array &$subjects): void
    {
        $annotation = $reader->getClassAnnotation($reflectionClass, Subject::class);
        if ($annotation instanceof Subject) {
            $subjects[$annotation->getName()] = $reflectionClass->getName();
        }
    }

    protected function buildPlace(Reader $reader, ReflectionClass $reflectionClass, ReflectionMethod $reflectionMethod, array &$places): void
    {
        $annotation = $reader->getMethodAnnotation($reflectionMethod, Place::class);
        if ($annotation instanceof Place) {
            $places[$reflectionClass->getName()][$annotation->getName()] = $reflectionMethod->getName();
        }
    }

    protected function buildTransition(Reader $reader, ReflectionClass $reflectionClass, ReflectionMethod $reflectionMethod, array &$transitions): void
    {
        $annotation = $reader->getMethodAnnotation($reflectionMethod, Transition::class);
        if ($annotation instanceof Transition) {
            $transitions[$reflectionClass->getName()][$annotation->getName()] = $reflectionMethod->getName();
        }
    }

    protected function bindings(ContainerBuilder $container, array $subjects, array $places, array $transitions): void
    {
        $subjectManagerDefinition = $container->getDefinition(SubjectManager::class);
        $subjectManagerDefinition->setBindings(['array $subjects' => $subjects]);

        $subjectHelperDefinition = $container->getDefinition(SubjectHelper::class);
        $subjectHelperDefinition->setBindings([
            'array $places' => $places,
            'array $transitions' => $transitions,
        ]);
    }
}

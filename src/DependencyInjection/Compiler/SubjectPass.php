<?php

namespace Tienvx\Bundle\MbtBundle\DependencyInjection\Compiler;

use Doctrine\Common\Annotations\Reader;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Tienvx\Bundle\MbtBundle\DependencyInjection\Compiler\Annotation\Builder;
use Tienvx\Bundle\MbtBundle\EventListener\WorkflowSubscriber;
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
        $builder = new Builder($container->get(Reader::class));
        foreach ($container->findTaggedServiceIds($this->subjectTag, true) as $serviceId => $attributes) {
            $definition = $container->getDefinition($serviceId);
            $subjectClass = $definition->getClass();
            $builder->build($subjectClass, $subjects, $places, $transitions);
        }

        $this->bindings($container, $subjects, $places, $transitions);
    }

    protected function bindings(ContainerBuilder $container, array $subjects, array $places, array $transitions): void
    {
        $subjectManagerDefinition = $container->getDefinition(SubjectManager::class);
        $subjectManagerDefinition->setBindings(['array $subjects' => $subjects]);

        $subjectHelperDefinition = $container->getDefinition(WorkflowSubscriber::class);
        $subjectHelperDefinition->setBindings([
            'array $places' => $places,
            'array $transitions' => $transitions,
        ]);
    }
}

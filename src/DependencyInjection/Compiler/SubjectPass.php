<?php

namespace Tienvx\Bundle\MbtBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class SubjectPass implements CompilerPassInterface
{
    /**
     * @var string
     */
    private $subjectService;

    /**
     * @var string
     */
    private $subjectTag;

    public function __construct(
        string $subjectService = 'mbt.subject_manager',
        string $subjectTag = 'mbt.subject'
    ) {
        $this->subjectService = $subjectService;
        $this->subjectTag = $subjectTag;
    }

    public function process(ContainerBuilder $container): void
    {
        if (!$container->hasDefinition($this->subjectService)) {
            return;
        }

        $services = [];
        foreach ($container->findTaggedServiceIds($this->subjectTag, true) as $serviceId => $attributes) {
            $definition = $container->getDefinition($serviceId);
            $services[] = $definition->getClass();
        }

        $subjectDefinition = $container->getDefinition($this->subjectService);
        $subjectDefinition->addMethodCall('setSubjects', [$services]);
    }
}

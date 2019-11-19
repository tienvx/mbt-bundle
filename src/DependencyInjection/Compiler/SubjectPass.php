<?php

namespace Tienvx\Bundle\MbtBundle\DependencyInjection\Compiler;

use Exception;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class SubjectPass implements CompilerPassInterface
{
    private $subjectService;
    private $subjectTag;

    public function __construct(
        string $subjectService = 'mbt.subject_manager',
        string $subjectTag = 'mbt.subject'
    ) {
        $this->subjectService = $subjectService;
        $this->subjectTag = $subjectTag;
    }

    /**
     * @throws Exception
     */
    public function process(ContainerBuilder $container)
    {
        if (!$container->hasDefinition($this->subjectService)) {
            return;
        }

        $services = [];
        foreach ($container->findTaggedServiceIds($this->subjectTag, true) as $serviceId => $attributes) {
            $def = $container->getDefinition($serviceId);
            $services[] = $def->getClass();
        }

        $subjectDefinition = $container->getDefinition($this->subjectService);
        $subjectDefinition->addMethodCall('setSubjects', [$services]);
    }
}

<?php

namespace Tienvx\Bundle\MbtBundle\DependencyInjection\Compiler;

use Exception;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class SubjectPass implements CompilerPassInterface
{
    use TaggedServiceTrait;

    private $subjectService;
    private $subjectTag;

    public function __construct(string $subjectService = 'mbt.subject_manager', string $subjectTag = 'mbt.subject')
    {
        $this->subjectService = $subjectService;
        $this->subjectTag = $subjectTag;
    }

    /**
     * @param ContainerBuilder $container
     *
     * @throws Exception
     */
    public function process(ContainerBuilder $container)
    {
        if (!$container->hasDefinition($this->subjectService)) {
            return;
        }

        $subjects = $this->findTaggedServices($container, $this->subjectTag, false);

        $subjectDefinition = $container->getDefinition($this->subjectService);
        $subjectDefinition->replaceArgument(0, $subjects);
    }
}

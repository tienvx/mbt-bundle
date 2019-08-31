<?php

namespace Tienvx\Bundle\MbtBundle\Generator;

use Symfony\Component\Workflow\Workflow;
use Tienvx\Bundle\MbtBundle\DependencyInjection\Compiler\PluginInterface;
use Tienvx\Bundle\MbtBundle\Entity\GeneratorOptions;
use Tienvx\Bundle\MbtBundle\Subject\SubjectInterface;

interface GeneratorInterface extends PluginInterface
{
    /**
     * @param Workflow         $workflow
     * @param SubjectInterface $subject
     * @param GeneratorOptions $generatorOptions
     *
     * @return iterable
     */
    public function generate(Workflow $workflow, SubjectInterface $subject, GeneratorOptions $generatorOptions = null): iterable;

    public function getLabel(): string;
}

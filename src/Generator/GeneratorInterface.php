<?php

namespace Tienvx\Bundle\MbtBundle\Generator;

use Generator;
use Symfony\Component\Workflow\Workflow;
use Tienvx\Bundle\MbtBundle\DependencyInjection\Compiler\PluginInterface;
use Tienvx\Bundle\MbtBundle\Subject\AbstractSubject;

interface GeneratorInterface extends PluginInterface
{
    /**
     * @param Workflow        $workflow
     * @param AbstractSubject $subject
     * @param array           $metaData
     *
     * @return Generator
     */
    public function getAvailableTransitions(Workflow $workflow, AbstractSubject $subject, array $metaData = null): Generator;

    public function applyTransition(Workflow $workflow, AbstractSubject $subject, string $transitionName): bool;
}

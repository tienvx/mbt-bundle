<?php

namespace Tienvx\Bundle\MbtBundle\Generator;

use Generator;
use Symfony\Component\Workflow\Workflow;
use Tienvx\Bundle\MbtBundle\DependencyInjection\Compiler\PluginInterface;
use Tienvx\Bundle\MbtBundle\Subject\Subject;

interface GeneratorInterface extends PluginInterface
{
    public function getAvailableTransitions(Workflow $workflow, Subject $subject): Generator;

    public function applyTransition(Workflow $workflow, Subject $subject, string $transitionName): bool;
}

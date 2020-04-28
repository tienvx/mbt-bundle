<?php

namespace Tienvx\Bundle\MbtBundle\Helper;

use Symfony\Component\Workflow\Definition;
use Symfony\Component\Workflow\Registry;
use Symfony\Component\Workflow\WorkflowInterface;
use Tienvx\Bundle\MbtBundle\Model\Subject;

class WorkflowHelper
{
    /**
     * @var Registry
     */
    protected $workflowRegistry;

    public function __construct(Registry $workflowRegistry)
    {
        $this->workflowRegistry = $workflowRegistry;
    }

    public function has(string $workflowName): bool
    {
        return $this->workflowRegistry->get(new Subject(), $workflowName) instanceof WorkflowInterface;
    }

    public function get(string $workflowName): WorkflowInterface
    {
        return $this->workflowRegistry->get(new Subject(), $workflowName);
    }

    public function getDefinition(string $workflowName): Definition
    {
        return $this->workflowRegistry->get(new Subject(), $workflowName)->getDefinition();
    }

    public function all(): array
    {
        return $this->workflowRegistry->all(new Subject());
    }

    public function count(): int
    {
        return count($this->all());
    }

    public function checksum(string $workflowName): string
    {
        $definition = $this->getDefinition($workflowName);
        $transitions = [];
        foreach ($definition->getTransitions() as $transition) {
            $transitions[] = [
                0 => $transition->getName(),
                1 => $transition->getFroms(),
                2 => $transition->getTos(),
            ];
        }
        $content = [
            0 => $definition->getPlaces(),
            1 => $transitions,
            2 => $definition->getInitialPlaces(),
        ];

        return md5(json_encode($content));
    }
}

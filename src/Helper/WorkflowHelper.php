<?php

namespace Tienvx\Bundle\MbtBundle\Helper;

use Exception;
use Symfony\Component\Workflow\Exception\InvalidArgumentException;
use Symfony\Component\Workflow\Registry;
use Symfony\Component\Workflow\Transition;
use Symfony\Component\Workflow\Workflow;
use Tienvx\Bundle\MbtBundle\Subject\AbstractSubject;
use Tienvx\Bundle\MbtBundle\Subject\SubjectInterface;

class WorkflowHelper
{
    /**
     * @var Registry
     */
    protected $workflowRegistry;

    public function setWorkflowRegistry(Registry $workflowRegistry): void
    {
        $this->workflowRegistry = $workflowRegistry;
    }

    /**
     * @throws Exception
     */
    public function get(string $model): Workflow
    {
        if (!$this->workflowRegistry instanceof Registry) {
            throw new Exception('No models were defined');
        }

        $subject = static::fakeSubject();

        try {
            return $this->workflowRegistry->get($subject, $model);
        } catch (InvalidArgumentException $exception) {
            throw new Exception(sprintf('Model "%s" does not exist', $model));
        }
    }

    /**
     * @return Workflow[]
     */
    public function all(): array
    {
        if (!$this->workflowRegistry instanceof Registry) {
            return [];
        }

        $subject = static::fakeSubject();

        return $this->workflowRegistry->all($subject);
    }

    public function checksum(Workflow $workflow): string
    {
        $definition = $workflow->getDefinition();
        $content = [
            0 => $definition->getPlaces(),
            1 => array_map(static function (Transition $transition) {
                return [
                    0 => $transition->getName(),
                    1 => $transition->getFroms(),
                    2 => $transition->getTos(),
                ];
            }, $definition->getTransitions()),
            2 => $definition->getInitialPlaces(),
        ];

        return md5(json_encode($content));
    }

    private static function fakeSubject(): SubjectInterface
    {
        return new class() extends AbstractSubject {
        };
    }
}

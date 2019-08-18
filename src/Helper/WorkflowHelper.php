<?php

namespace Tienvx\Bundle\MbtBundle\Helper;

use Exception;
use Symfony\Component\Workflow\Exception\InvalidArgumentException;
use Symfony\Component\Workflow\Registry;
use Symfony\Component\Workflow\Transition;
use Symfony\Component\Workflow\Workflow;
use Tienvx\Bundle\MbtBundle\Entity\Steps;
use Tienvx\Bundle\MbtBundle\Subject\AbstractSubject;

class WorkflowHelper
{
    /**
     * @param Registry $registry
     * @param string   $model
     *
     * @return Workflow
     *
     * @throws Exception
     */
    public static function get(Registry $registry, string $model): Workflow
    {
        $subject = static::fakeSubject();

        try {
            return $registry->get($subject, $model);
        } catch (InvalidArgumentException $exception) {
            throw new Exception(sprintf('Model "%s" does not exist', $model));
        }
    }

    /**
     * @param Registry $registry
     *
     * @return Workflow[]
     */
    public static function all(Registry $registry): array
    {
        $subject = static::fakeSubject();

        return $registry->all($subject);
    }

    public static function checksum(Workflow $workflow)
    {
        $definition = $workflow->getDefinition();
        $content = [
            'places' => $definition->getPlaces(),
            'transitions' => array_map(function (Transition $transition) {
                return [
                    'name' => $transition->getName(),
                    'froms' => $transition->getFroms(),
                    'tos' => $transition->getTos(),
                ];
            }, $definition->getTransitions()),
            'initialPlaces' => $definition->getInitialPlaces(),
        ];

        return md5(json_encode($content));
    }

    /**
     * @param Steps    $steps
     * @param Workflow $workflow
     *
     * @return bool
     */
    public static function validate(Steps $steps, Workflow $workflow): bool
    {
        $definition = $workflow->getDefinition();
        $transitions = array_map(function (Transition $transition) {
            return $transition->getName();
        }, $definition->getTransitions());

        foreach ($steps as $step) {
            if (null !== $step->getTransition() && !in_array($step->getTransition(), $transitions)) {
                return false;
            }
        }

        return true;
    }

    private static function fakeSubject()
    {
        return new class() extends AbstractSubject {
            public static function support(): bool
            {
                return true;
            }

            public static function getName(): string
            {
                return '';
            }
        };
    }
}

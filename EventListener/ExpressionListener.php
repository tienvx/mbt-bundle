<?php

namespace Tienvx\Bundle\MbtBundle\EventListener;

use Symfony\Component\ExpressionLanguage\ExpressionLanguage;
use Symfony\Component\Workflow\Event\GuardEvent;
use Symfony\Component\Workflow\Event\Event;
use Tienvx\Bundle\MbtBundle\Model\Transition;

class ExpressionListener
{
    private $configuration;
    private $expressionLanguage;

    public function __construct($configuration, ExpressionLanguage $expressionLanguage)
    {
        $this->configuration = $configuration;
        $this->expressionLanguage = $expressionLanguage;
    }

    public function onGuard(GuardEvent $event, $eventName)
    {
        if (!isset($this->configuration['guard'][$eventName])) {
            return;
        }

        if (!$this->expressionLanguage->evaluate($this->configuration['guard'][$eventName], [
            'subject' => $event->getSubject(),
        ])) {
            $event->setBlocked(true);
        }
    }

    public function onTransition(Event $event, $eventName)
    {
        if (!isset($this->configuration['data'][$eventName])) {
            return;
        }

        $data = [];
        foreach ($this->configuration['data'][$eventName] as $key => $expression) {
            $data[$key] = $this->expressionLanguage->evaluate($expression, [
                'subject' => $event->getSubject(),
            ]);
        }
        $transition = $event->getTransition();
        if ($transition instanceof Transition) {
            $transition->setData($data);
        }
    }
}

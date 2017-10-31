<?php

namespace Tienvx\Bundle\MbtBundle\EventListener;

use Symfony\Component\ExpressionLanguage\ExpressionLanguage;
use Symfony\Component\Workflow\Event\GuardEvent;

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
        if (!isset($this->configuration[$eventName])) {
            return;
        }

        if (!$this->expressionLanguage->evaluate($this->configuration[$eventName], [
            'subject' => $event->getSubject(),
        ])) {
            $event->setBlocked(true);
        }
    }
}

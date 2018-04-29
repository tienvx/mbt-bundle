<?php

namespace Tienvx\Bundle\MbtBundle\EventListener;

use Symfony\Component\ExpressionLanguage\ExpressionLanguage;
use Symfony\Component\Workflow\Event\GuardEvent;

class ModelGuardListener
{
    private $configuration;
    private $expressionLanguage;

    public function __construct(ExpressionLanguage $expressionLanguage, array $configuration = [])
    {
        $this->configuration = $configuration;
        $this->expressionLanguage = $expressionLanguage;
    }

    public function onGuard(GuardEvent $event, $eventName)
    {
        if (!isset($this->configuration[$eventName])) {
            return;
        }

        $subject = $event->getSubject();
        if (!$this->expressionLanguage->evaluate($this->configuration[$eventName], [
            'subject' => $subject,
        ])) {
            $event->setBlocked(true);
        }
    }
}

<?php

namespace Tienvx\Bundle\MbtBundle\EventListener;

use Symfony\Component\ExpressionLanguage\ExpressionLanguage;
use Symfony\Component\Workflow\Event\GuardEvent;
use Symfony\Component\Workflow\TransitionBlocker;

class ModelGuardListener
{
    private $configuration;
    private $expressionLanguage;

    public function __construct(array $configuration = [], ExpressionLanguage $expressionLanguage)
    {
        $this->configuration = $configuration;
        $this->expressionLanguage = $expressionLanguage;
    }

    public function onTransition(GuardEvent $event, $eventName)
    {
        if (!isset($this->configuration[$eventName])) {
            return;
        }

        $expression = $this->configuration[$eventName];

        // for model, guard expression only support 'subject' variable
        if (!$this->expressionLanguage->evaluate($expression, [
            'subject' => $event->getSubject(),
        ])) {
            $blocker = TransitionBlocker::createBlockedByExpressionGuardListener($expression);
            $event->addTransitionBlocker($blocker);
        }
    }
}

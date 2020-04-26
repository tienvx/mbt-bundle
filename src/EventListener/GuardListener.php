<?php

namespace Tienvx\Bundle\MbtBundle\EventListener;

use Symfony\Component\ExpressionLanguage\ExpressionLanguage;
use Symfony\Component\Workflow\Event\GuardEvent;
use Symfony\Component\Workflow\EventListener\GuardExpression;
use Symfony\Component\Workflow\TransitionBlocker;

class GuardListener
{
    private $configuration;
    private $expressionLanguage;

    public function __construct(array $configuration, ExpressionLanguage $expressionLanguage)
    {
        $this->configuration = $configuration;
        $this->expressionLanguage = $expressionLanguage;
    }

    public function onTransition(GuardEvent $event, string $eventName)
    {
        if (!isset($this->configuration[$eventName])) {
            return;
        }

        $eventConfiguration = (array) $this->configuration[$eventName];
        foreach ($eventConfiguration as $guard) {
            if ($guard instanceof GuardExpression) {
                if ($guard->getTransition() !== $event->getTransition()) {
                    continue;
                }
                $this->validateGuardExpression($event, $guard->getExpression());
            } else {
                $this->validateGuardExpression($event, $guard);
            }
        }
    }

    private function validateGuardExpression(GuardEvent $event, string $expression)
    {
        if (!$this->expressionLanguage->evaluate($expression, ['subject' => $event->getSubject()])) {
            $blocker = TransitionBlocker::createBlockedByExpressionGuardListener($expression);
            $event->addTransitionBlocker($blocker);
        }
    }
}

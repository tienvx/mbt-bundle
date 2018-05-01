<?php

namespace Tienvx\Bundle\MbtBundle\EventListener;

use Symfony\Component\ExpressionLanguage\ExpressionLanguage;
use Symfony\Component\Workflow\Event\GuardEvent;
use Symfony\Component\Workflow\TransitionBlocker;
use Tienvx\Bundle\MbtBundle\Model\Subject;

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

        $subject = $event->getSubject();
        if (!$subject instanceof Subject) {
            return;
        }

        if ($subject->isAnnouncing()) {
            // for models, next available transition will be selected by generator, so triggering annouce events
            // is not necessary
            $event->setBlocked(true);
        }
        else {
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
}

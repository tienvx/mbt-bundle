<?php

namespace Tienvx\Bundle\MbtBundle\Helper;

use Symfony\Component\Workflow\EventListener\ExpressionLanguage;
use Symfony\Component\Workflow\EventListener\GuardExpression;
use Tienvx\Bundle\MbtBundle\Subject\SubjectInterface;

class GuardHelper
{
    private $configuration = [];
    private $expressionLanguage;

    public function setExpressionLanguage(ExpressionLanguage $expressionLanguage): void
    {
        $this->expressionLanguage = $expressionLanguage;
    }

    public function mergeConfiguration(array $configuration): void
    {
        $this->configuration += $configuration;
    }

    public function can(SubjectInterface $subject, string $model, string $transition): bool
    {
        $key = sprintf('workflow.%s.guard.%s', $model, $transition);
        if (!isset($this->configuration[$key])) {
            return true;
        }

        $guards = (array) $this->configuration[$key];
        foreach ($guards as $guard) {
            if (!$this->validateGuard($guard, $subject, $transition)) {
                return false;
            }
        }

        return true;
    }

    protected function validateGuard($guard, SubjectInterface $subject, string $transition): bool
    {
        if ($guard instanceof GuardExpression) {
            if ($guard->getTransition()->getName() !== $transition) {
                return true;
            }
            if (!$this->validateGuardExpression($subject, $guard->getExpression())) {
                return false;
            }
        } else {
            return $this->validateGuardExpression($subject, $guard);
        }

        return true;
    }

    protected function validateGuardExpression(SubjectInterface $subject, string $expression): bool
    {
        return $this->expressionLanguage->evaluate($expression, ['subject' => $subject]);
    }
}

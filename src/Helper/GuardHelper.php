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
            if ($guard instanceof GuardExpression &&
                $guard->getTransition() !== $transition &&
                !$this->expressionLanguage->evaluate($guard->getExpression(), ['subject' => $subject])) {
                return false;
            }
        }

        return true;
    }
}

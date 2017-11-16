<?php

namespace Tienvx\Bundle\MbtBundle\Service;

use Symfony\Component\ExpressionLanguage\ExpressionLanguage;
use Tienvx\Bundle\MbtBundle\Subject\Subject;

class DataProvider
{
    protected $configuration;
    protected $expressionLanguage;

    public function __construct($configuration, ExpressionLanguage $expressionLanguage)
    {
        $this->configuration = $configuration;
        $this->expressionLanguage = $expressionLanguage;
    }

    public function getData(Subject $subject, $model, $transition): array
    {
        $data = [];
        if (isset($this->configuration[$model][$transition])) {
            foreach ($this->configuration[$model][$transition] as $key => $expression) {
                $data[$key] = $this->expressionLanguage->evaluate($expression, [
                    'subject' => $subject,
                ]);
            }
        }
        return $data;
    }
}
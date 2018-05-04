<?php

namespace Tienvx\Bundle\MbtBundle\Generator;

use Fhaculty\Graph\Edge\Directed;
use Symfony\Component\Workflow\Registry;
use Tienvx\Bundle\MbtBundle\DependencyInjection\Compiler\PluginInterface;
use Tienvx\Bundle\MbtBundle\Graph\Path;
use Tienvx\Bundle\MbtBundle\Model\Subject;
use Tienvx\Bundle\MbtBundle\Service\GraphBuilder;
use Tienvx\Bundle\MbtBundle\Service\StopConditionManager;

interface GeneratorInterface extends PluginInterface
{
    public function __construct(Registry $workflows, GraphBuilder $graphBuilder, StopConditionManager $stopConditionManager);

    public function init(string $model, string $subject, array $arguments, bool $generatingSteps = false);

    public function canGoNextStep(Directed $currentEdge): bool;

    public function getNextStep(): ?Directed;

    public function goToNextStep(Directed $edge);

    public function meetStopCondition(): bool;

    public function getSubject(): Subject;

    public function getPath(): Path;
}

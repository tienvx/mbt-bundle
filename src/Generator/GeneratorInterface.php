<?php

namespace Tienvx\Bundle\MbtBundle\Generator;

use Fhaculty\Graph\Edge\Directed;
use Tienvx\Bundle\MbtBundle\DependencyInjection\Compiler\PluginInterface;
use Tienvx\Bundle\MbtBundle\Graph\Path;
use Tienvx\Bundle\MbtBundle\Model\Model;
use Tienvx\Bundle\MbtBundle\Service\DataProvider;
use Tienvx\Bundle\MbtBundle\Service\GraphBuilder;
use Tienvx\Bundle\MbtBundle\Service\StopConditionManager;

interface GeneratorInterface extends PluginInterface
{
    public function __construct(DataProvider $dataProvider, GraphBuilder $graphBuilder, StopConditionManager $stopConditionManager);

    public function init(Model $model, array $arguments);

    public function canGoNextStep(Directed $currentEdge): bool;

    public function getNextStep(): ?Directed;

    public function goToNextStep(Directed $edge, bool $callSUT = false);

    public function meetStopCondition(): bool;

    public function getPath(): Path;
}

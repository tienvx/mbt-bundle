<?php

namespace Tienvx\Bundle\MbtBundle\Generator;

use Fhaculty\Graph\Edge\Directed;
use Tienvx\Bundle\MbtBundle\Graph\Path;
use Tienvx\Bundle\MbtBundle\Model\Model;
use Tienvx\Bundle\MbtBundle\Service\DataProvider;
use Tienvx\Bundle\MbtBundle\Service\GraphBuilder;

interface GeneratorInterface
{
    public function __construct(DataProvider $dataProvider, GraphBuilder $graphBuilder);

    public function setArgs(array $args);

    public function setModel(Model $model);

    public function init();

    public function canGoNextStep(Directed $currentEdge): bool;

    public function getNextStep(): ?Directed;

    public function goToNextStep(Directed $edge, bool $callSUT = false);

    public function getMaxProgress(): int;

    public function getCurrentProgress(): int;

    public function getCurrentProgressMessage(): string;

    public function meetStopCondition(): bool;

    public function getPath(): Path;
}

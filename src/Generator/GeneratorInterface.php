<?php

namespace Tienvx\Bundle\MbtBundle\Generator;

use Fhaculty\Graph\Edge\Directed;
use Tienvx\Bundle\MbtBundle\DependencyInjection\Compiler\PluginInterface;
use Tienvx\Bundle\MbtBundle\Graph\Path;
use Tienvx\Bundle\MbtBundle\Model\Model;
use Tienvx\Bundle\MbtBundle\Model\Subject;
use Tienvx\Bundle\MbtBundle\Service\GraphBuilder;
use Tienvx\Bundle\MbtBundle\StopCondition\StopConditionInterface;

interface GeneratorInterface extends PluginInterface
{
    public function __construct(GraphBuilder $graphBuilder);

    public function init(Model $model, Subject $subject, StopConditionInterface $stopCondition);

    public function getNextStep(): ?Directed;

    public function goToNextStep(Directed $edge): bool;

    public function meetStopCondition(): bool;

    public function getSubject(): Subject;

    public function getPath(): Path;
}

<?php

namespace Tienvx\Bundle\MbtBundle\PathReducer;

use Symfony\Component\Workflow\Registry;
use Tienvx\Bundle\MbtBundle\DependencyInjection\Compiler\PluginInterface;
use Tienvx\Bundle\MbtBundle\Entity\Bug;
use Tienvx\Bundle\MbtBundle\Graph\Path;
use Tienvx\Bundle\MbtBundle\Message\ReductionMessage;

interface PathReducerInterface extends PluginInterface
{
    public function reduce(Bug $bug);

    public function handle(ReductionMessage $message);

    public function setWorkflowRegistry(Registry $workflowRegistry);

    public function dispatch(int $bugId, Path $newPath = null, ReductionMessage $message = null): int;
}

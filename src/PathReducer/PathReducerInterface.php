<?php

namespace Tienvx\Bundle\MbtBundle\PathReducer;

use Tienvx\Bundle\MbtBundle\DependencyInjection\Compiler\PluginInterface;
use Tienvx\Bundle\MbtBundle\Entity\Bug;
use Tienvx\Bundle\MbtBundle\Entity\Path;

interface PathReducerInterface extends PluginInterface
{
    public function reduce(Bug $bug);

    public function handle(int $bugId, int $length, int $from, int $to);

    public function dispatch(int $bugId, Path $newPath = null): int;

    public function getLabel(): string;
}

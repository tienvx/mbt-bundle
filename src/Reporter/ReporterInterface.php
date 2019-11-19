<?php

namespace Tienvx\Bundle\MbtBundle\Reporter;

use Tienvx\Bundle\MbtBundle\DependencyInjection\Compiler\PluginInterface;
use Tienvx\Bundle\MbtBundle\Entity\Bug;

interface ReporterInterface extends PluginInterface
{
    public function report(Bug $bug);

    public function getLabel(): string;
}

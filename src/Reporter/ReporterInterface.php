<?php

namespace Tienvx\Bundle\MbtBundle\Reporter;

use Tienvx\Bundle\MbtBundle\Plugin\PluginInterface;

interface ReporterInterface extends PluginInterface
{
    public function getLabel(): string;
}

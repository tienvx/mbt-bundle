<?php

namespace Tienvx\Bundle\MbtBundle\Reporter;

use Tienvx\Bundle\MbtBundle\Entity\Bug;
use Tienvx\Bundle\MbtBundle\Plugin\PluginInterface;

interface ReporterInterface extends PluginInterface
{
    public function report(Bug $bug): void;

    public function getLabel(): string;
}

<?php

namespace Tienvx\Bundle\MbtBundle\Generator;

use Tienvx\Bundle\MbtBundle\Model\Petrinet\PetrinetInterface;
use Tienvx\Bundle\MbtBundle\Plugin\PluginInterface;

interface GeneratorInterface extends PluginInterface
{
    public function generate(PetrinetInterface $petrinet): iterable;
}

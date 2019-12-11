<?php

namespace Tienvx\Bundle\MbtBundle\Generator;

use Tienvx\Bundle\MbtBundle\Entity\GeneratorOptions;
use Tienvx\Bundle\MbtBundle\Model\Model;
use Tienvx\Bundle\MbtBundle\Plugin\PluginInterface;
use Tienvx\Bundle\MbtBundle\Subject\SubjectInterface;

interface GeneratorInterface extends PluginInterface
{
    public function generate(Model $model, SubjectInterface $subject, GeneratorOptions $generatorOptions): iterable;
}

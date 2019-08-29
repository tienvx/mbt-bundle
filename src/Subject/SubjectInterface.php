<?php

namespace Tienvx\Bundle\MbtBundle\Subject;

use Tienvx\Bundle\MbtBundle\DependencyInjection\Compiler\PluginInterface;

interface SubjectInterface extends PluginInterface
{
    public function setUp(bool $testing = false);

    public function tearDown();
}

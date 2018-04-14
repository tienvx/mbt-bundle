<?php

namespace Tienvx\Bundle\MbtBundle\Tests\Command;

use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class CommandTestCase extends KernelTestCase
{
    protected function getCoverageStopCondition($edgeCoverage, $vertexCoverage)
    {
        return sprintf('{"stop":{"on":"coverage","at":{"edgeCoverage":%d,"vertexCoverage":%d}}}', $edgeCoverage, $vertexCoverage);
    }

    protected function getFoundBugStopCondition()
    {
        return '{"stop":{"on":"found-bug"}}';
    }
}

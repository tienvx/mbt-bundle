<?php

namespace Tienvx\Bundle\MbtBundle\Tests\Command;

use Tienvx\Bundle\MbtBundle\Tests\AbstractTestCase;

class CommandTestCase extends AbstractTestCase
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

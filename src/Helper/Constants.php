<?php

namespace Tienvx\Bundle\MbtBundle\Helper;

class Constants
{
    const DEFAULT_GENERATOR = 'random';
    const DEFAULT_STOP_CONDITION = 'coverage';
    const DEFAULT_STOP_CONDITION_ARGUMENTS = '{"edgeCoverage":100,"vertexCoverage":100}';
    const DEFAULT_REDUCER = 'weighted-random';
}

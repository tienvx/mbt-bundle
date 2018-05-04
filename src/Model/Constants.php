<?php

namespace Tienvx\Bundle\MbtBundle\Model;

class Constants
{
    const DEFAULT_GENERATOR = 'random';
    const DEFAULT_ARGUMENTS = '{"stop":{"on":"coverage","at":{"edgeCoverage":100,"vertexCoverage":100}}}';
    const DEFAULT_REDUCER   = 'weighted-random';
}

<?php

namespace Tienvx\Bundle\MbtBundle\Annotation;

use Doctrine\Common\Annotations\Annotation;
use Doctrine\Common\Annotations\Annotation\Target;
use Doctrine\Common\Annotations\Annotation\Required;

/**
 * @Annotation
 * @Target({"METHOD"})
 */
class DataProvider
{
    /** @Required */
    public $method;
}

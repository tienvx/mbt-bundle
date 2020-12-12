<?php

namespace Tienvx\Bundle\MbtBundle\Validator;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */
class ValidSeleniumConfig extends Constraint
{
    public const IS_SELENIUM_CONFIG_INVALID_ERROR = 'c598e3c4-80c3-4e68-85cb-08de9beceaff';

    protected static $errorNames = [
        self::IS_SELENIUM_CONFIG_INVALID_ERROR => 'IS_SELENIUM_CONFIG_INVALID_ERROR',
    ];

    public string $message = 'The selenium config is not valid.';

    public function getTargets()
    {
        return self::CLASS_CONSTRAINT;
    }
}

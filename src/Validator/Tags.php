<?php

namespace Tienvx\Bundle\MbtBundle\Validator;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */
class Tags extends Constraint
{
    public const IS_TAGS_INVALID_ERROR = '628fca96-35f8-11eb-adc1-0242ac120002';

    protected static $errorNames = [
        self::IS_TAGS_INVALID_ERROR => 'IS_TAGS_INVALID_ERROR',
    ];

    public string $message = 'The tags should be unique and not blank.';
}

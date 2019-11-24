<?php

namespace Tienvx\Bundle\MbtBundle\Annotation;

use InvalidArgumentException;

abstract class AbstractAnnotation
{
    /**
     * @var string
     */
    private $name;

    public function __construct(array $options)
    {
        if (isset($options['value'])) {
            $options['name'] = $options['value'];
            unset($options['value']);
        }

        foreach ($options as $key => $value) {
            if (!property_exists($this, $key)) {
                throw new InvalidArgumentException(sprintf('Property "%s" does not exist', $key));
            }

            $this->$key = $value;
        }
    }

    public function getName(): string
    {
        return $this->name;
    }
}

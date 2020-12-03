<?php

namespace Tienvx\Bundle\MbtBundle\ValueObject\Model;

use Symfony\Component\Validator\Constraints as Assert;
use Tienvx\Bundle\MbtBundle\Model\Model\Command as CommandModel;

class Command extends CommandModel
{
    /**
     * @Assert\NotBlank
     * @Assert\Type("string")
     */
    protected string $command;

    /**
     * @Assert\NotBlank
     * @Assert\Type("string")
     */
    protected string $target;

    /**
     * @Assert\Type("string")
     */
    protected ?string $value = null;
}

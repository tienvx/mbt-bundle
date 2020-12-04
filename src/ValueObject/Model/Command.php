<?php

namespace Tienvx\Bundle\MbtBundle\ValueObject\Model;

use Symfony\Component\Validator\Constraints as Assert;
use Tienvx\Bundle\MbtBundle\Model\Model\Command as CommandModel;
use Tienvx\Bundle\MbtBundle\Model\Model\CommandInterface;

class Command extends CommandModel
{
    /**
     * @Assert\NotBlank
     * @Assert\Choice(choices=CommandInterface::ALL_COMMANDS)
     */
    protected string $command;

    /**
     * @Assert\NotBlank
     */
    protected string $target;

    protected ?string $value = null;
}

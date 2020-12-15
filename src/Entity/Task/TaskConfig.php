<?php

namespace Tienvx\Bundle\MbtBundle\Entity\Task;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Tienvx\Bundle\MbtBundle\Model\Task\TaskConfig as TaskConfigModel;
use Tienvx\Bundle\MbtBundle\Validator\ValidTaskConfig;

/**
 * @ORM\Embeddable
 * @ValidTaskConfig
 */
class TaskConfig extends TaskConfigModel
{
    /**
     * @ORM\Column(type="string")
     * @Assert\NotBlank
     */
    protected string $generator = '';

    /**
     * @ORM\Column(type="array")
     */
    protected array $generatorConfig = [];

    /**
     * @ORM\Column(type="string")
     * @Assert\NotBlank
     */
    protected string $reducer = '';

    /**
     * @ORM\Column(type="boolean")
     */
    protected bool $sendEmail;

    /**
     * @ORM\Column(type="array")
     * @Assert\Unique
     */
    protected array $notifyChannels = [];
}

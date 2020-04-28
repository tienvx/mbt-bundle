<?php

namespace Tienvx\Bundle\MbtBundle\Entity;

use DateTime;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Tienvx\Bundle\MbtBundle\Model\Task as TaskModel;
use Tienvx\Bundle\MbtBundle\Validator\Constraints as MbtAssert;

/**
 * @ORM\Entity
 * @ORM\HasLifecycleCallbacks
 */
class Task extends TaskModel
{
    /**
     * @ORM\Column(type="integer")
     * @ORM\Id()
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\Column(type="string")
     * @Assert\Type("string")
     * @Assert\NotBlank
     */
    protected $title;

    /**
     * @ORM\Embedded(class="Workflow")
     * @Assert\Valid
     * @MbtAssert\Workflow
     */
    protected $workflow;

    /**
     * @ORM\Embedded(class="Generator")
     * @Assert\Valid
     * @MbtAssert\Generator
     */
    protected $generator;

    /**
     * @ORM\Embedded(class="GeneratorOptions")
     * @Assert\Valid
     */
    protected $generatorOptions;

    /**
     * @ORM\Embedded(class="Reducer")
     * @Assert\Valid
     * @MbtAssert\Reducer
     */
    protected $reducer;

    /**
     * @ORM\Column(type="text")
     * @Assert\Type("string")
     * @Assert\NotNull
     */
    protected $reporters;

    /**
     * @ORM\Column(type="string")
     * @Assert\Type("string")
     * @MbtAssert\TaskStatus
     */
    protected $status;

    /**
     * @ORM\OneToMany(targetEntity="Bug", mappedBy="task", cascade={"persist"})
     */
    protected $bugs;

    /**
     * @ORM\Column(type="boolean")
     * @Assert\Type("bool")
     */
    protected $takeScreenshots;

    /**
     * @ORM\Column(name="created_at", type="datetime", nullable=true)
     */
    protected $createdAt;

    /**
     * @ORM\Column(name="updated_at", type="datetime", nullable=true)
     */
    protected $updatedAt;

    /**
     * @MbtAssert\Reporters
     */
    public function getReporters(): array
    {
        return parent::getReporters();
    }

    /**
     * @ORM\PrePersist
     */
    public function prePersist(): void
    {
        $this->createdAt = new DateTime();
        $this->updatedAt = new DateTime();
    }

    /**
     * @ORM\PreUpdate
     */
    public function preUpdate(): void
    {
        $this->updatedAt = new DateTime();
    }
}

<?php

namespace Tienvx\Bundle\MbtBundle\Entity;

use DateTime;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Tienvx\Bundle\MbtBundle\Model\Bug as BugModel;
use Tienvx\Bundle\MbtBundle\Model\WorkflowInterface;
use Tienvx\Bundle\MbtBundle\Validator\Constraints as MbtAssert;

/**
 * @ORM\Entity
 * @ORM\HasLifecycleCallbacks
 */
class Bug extends BugModel
{
    /**
     * @ORM\Column(type="integer")
     * @ORM\Id()
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\Column(type="text")
     * @Assert\Type("string")
     * @Assert\NotBlank
     */
    protected $title;

    /**
     * @ORM\Column(type="string")
     * @Assert\Type("string")
     * @MbtAssert\BugStatus
     */
    protected $status;

    /**
     * @ORM\Column(type="text")
     * @Assert\Type("string")
     * @Assert\NotNull
     */
    protected $steps;

    /**
     * @ORM\Column(type="string")
     * @Assert\Type("string")
     * @Assert\NotBlank
     * @MbtAssert\Workflow
     */
    protected $workflow;

    /**
     * @ORM\Column(type="string")
     * @Assert\Type("string")
     * @Assert\NotBlank
     */
    protected $workflowHash;

    /**
     * @ORM\ManyToOne(targetEntity="Task")
     * @ORM\JoinColumn(name="task_id", referencedColumnName="id", nullable=true, onDelete="SET NULL")
     */
    protected $task;

    /**
     * @ORM\Column(type="text")
     * @Assert\Type("string")
     */
    protected $bugMessage;

    /**
     * @ORM\Column(type="integer", nullable=true)
     * @Assert\Type("integer")
     */
    protected $messagesCount = 0;

    /**
     * @ORM\Column(name="created_at", type="datetime", nullable=true)
     */
    protected $createdAt;

    /**
     * @ORM\Column(name="updated_at", type="datetime", nullable=true)
     */
    protected $updatedAt;

    public function getWorkflow(): WorkflowInterface
    {
        return new Workflow($this->workflow);
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

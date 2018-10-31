<?php

namespace Tienvx\Bundle\MbtBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Tienvx\Bundle\MbtBundle\Validator\Constraints as MbtAssert;

/**
 * @ORM\Entity
 */
class Bug
{
    /**
     * @ORM\Column(type="integer")
     * @ORM\Id()
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @ORM\Column(type="text")
     * @Assert\NotBlank
     */
    private $title;

    /**
     * @ORM\Column(type="string")
     * @Assert\Choice({"new", "reducing", "reported"})
     */
    private $status;

    /**
     * @ORM\Column(type="text")
     * @Assert\NotBlank
     * @MbtAssert\Path
     */
    private $path;

    /**
     * @ORM\Column(type="integer")
     * @Assert\NotBlank
     */
    private $length;

    /**
     * @ORM\ManyToOne(targetEntity="Task")
     * @ORM\JoinColumn(name="task_id", referencedColumnName="id")
     */
    private $task;

    /**
     * @ORM\Column(type="text")
     * @Assert\NotBlank
     */
    private $bugMessage;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $messagesCount;

    public function getId()
    {
        return $this->id;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function setTitle(string $title)
    {
        $this->title = $title;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function setStatus(string $status)
    {
        $this->status = $status;
    }

    public function getPath(): string
    {
        return $this->path;
    }

    public function setPath(string $path)
    {
        $this->path = $path;
    }

    public function getLength(): int
    {
        return $this->length;
    }

    public function setLength(int $length)
    {
        $this->length = $length;
    }

    public function getTask(): Task
    {
        return $this->task;
    }

    public function setTask(Task $task)
    {
        $this->task = $task;
    }

    public function getBugMessage(): string
    {
        return $this->bugMessage;
    }

    public function setBugMessage(string $bugMessage)
    {
        $this->bugMessage = $bugMessage;
    }

    public function getMessagesCount(): int
    {
        return $this->messagesCount;
    }

    public function setMessagesCount(int $messagesCount)
    {
        $this->messagesCount = $messagesCount;
    }
}

<?php

namespace Tienvx\Bundle\MbtBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table()
 * @ORM\Entity()
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
     * @ORM\ManyToOne(targetEntity="Task")
     * @ORM\JoinColumn(name="task_id", referencedColumnName="id")
     */
    private $task;

    /** @ORM\Column(type="string", length=255) */
    private $title;

    /** @ORM\Column(type="string", length=255) */
    private $message;

    /** @ORM\Column(type="array") */
    private $paths;

    /** @ORM\Column(type="smallint") */
    private $status;

    public function getId()
    {
        return $this->id;
    }

    public function getTask(): Task
    {
        return $this->task;
    }

    public function setTask(Task $task)
    {
        $this->task = $task;
    }

    public function getTitle()
    {
        return $this->title;
    }

    public function setTitle($title)
    {
        $this->title = $title;
    }

    public function getMessage()
    {
        return $this->message;
    }

    public function setMessage($message)
    {
        $this->message = $message;
    }

    public function getPaths(): array
    {
        return $this->paths;
    }

    public function setPaths(array $paths)
    {
        $this->paths = $paths;
    }

    public function addPath(string $path)
    {
        $this->paths[] = $path;
    }

    public function getStatus()
    {
        return $this->status;
    }

    public function setStatus($status)
    {
        $this->status = $status;
    }
}

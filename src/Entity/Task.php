<?php

namespace Tienvx\Bundle\MbtBundle\Entity;

use DateTime;
use DateTimeInterface;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\Embedded;
use Symfony\Component\Validator\Constraints as Assert;
use Tienvx\Bundle\MbtBundle\Model\Model\RevisionInterface;
use Tienvx\Bundle\MbtBundle\Model\Task as TaskModel;
use Tienvx\Bundle\MbtBundle\Model\Task\BrowserInterface;
use Tienvx\Bundle\MbtBundle\Repository\TaskRepository;

/**
 * @ORM\Entity(repositoryClass=TaskRepository::class)
 * @ORM\HasLifecycleCallbacks
 */
class Task extends TaskModel
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    protected ?int $id = null;

    /**
     * @ORM\Column(type="string")
     * @Assert\NotBlank
     */
    protected string $title = '';

    /**
     * @ORM\ManyToOne(targetEntity="\Tienvx\Bundle\MbtBundle\Entity\Model\Revision")
     * @ORM\JoinColumn(nullable=false)
     * @Assert\Valid
     * @Assert\NotNull
     */
    protected RevisionInterface $modelRevision;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    protected ?int $author = null;

    /**
     * @ORM\Column(type="boolean")
     */
    protected bool $running = false;

    /**
     * @Embedded(class="\Tienvx\Bundle\MbtBundle\Entity\Task\Browser")
     * @Assert\NotNull
     */
    protected BrowserInterface $browser;

    /**
     * @ORM\OneToMany(targetEntity="\Tienvx\Bundle\MbtBundle\Entity\Bug", mappedBy="task", cascade={"persist"})
     * @ORM\JoinColumn(onDelete="CASCADE")
     */
    protected Collection $bugs;

    /**
     * @ORM\Column(type="boolean")
     */
    protected bool $debug = false;

    /**
     * @ORM\Column(name="created_at", type="datetime")
     */
    protected DateTimeInterface $createdAt;

    /**
     * @ORM\Column(name="updated_at", type="datetime")
     */
    protected DateTimeInterface $updatedAt;

    /**
     * @ORM\PrePersist
     */
    public function prePersist(): void
    {
        $this->setCreatedAt(new DateTime());
        $this->setUpdatedAt(new DateTime());
    }

    /**
     * @ORM\PreUpdate
     */
    public function preUpdate(): void
    {
        $this->setUpdatedAt(new DateTime());
    }
}

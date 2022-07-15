<?php

namespace Tienvx\Bundle\MbtBundle\Entity;

use DateTime;
use DateTimeInterface;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Tienvx\Bundle\MbtBundle\Entity\Bug\Video;
use Tienvx\Bundle\MbtBundle\Model\Bug as BugModel;
use Tienvx\Bundle\MbtBundle\Model\Bug\VideoInterface;
use Tienvx\Bundle\MbtBundle\Model\ProgressInterface;
use Tienvx\Bundle\MbtBundle\Model\TaskInterface;
use Tienvx\Bundle\MbtBundle\Repository\BugRepository;
use Tienvx\Bundle\MbtBundle\ValueObject\Bug\Step;

#[ORM\Entity(repositoryClass: BugRepository::class)]
#[ORM\HasLifecycleCallbacks]
class Bug extends BugModel
{
    #[ORM\Column(type: 'integer')]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    protected ?int $id = null;

    #[ORM\Column(type: 'string')]
    #[Assert\NotBlank]
    protected string $title;

    #[ORM\Column(type: 'array')]
    #[Assert\All([
        new Assert\Type(type: Step::class),
    ])]
    #[Assert\Valid]
    protected array $steps = [];

    #[ORM\ManyToOne(targetEntity: Task::class, inversedBy: 'bugs')]
    #[ORM\JoinColumn(nullable: false)]
    protected TaskInterface $task;

    #[ORM\Column(type: 'text')]
    protected string $message;

    #[ORM\Embedded(class: Progress::class)]
    protected ProgressInterface $progress;

    #[ORM\Column(type: 'boolean')]
    protected bool $closed = false;

    #[ORM\Embedded(class: Video::class)]
    protected VideoInterface $video;

    #[ORM\Column(name: 'created_at', type: 'datetime')]
    protected DateTimeInterface $createdAt;

    #[ORM\Column(name: 'updated_at', type: 'datetime')]
    protected DateTimeInterface $updatedAt;

    public function __construct()
    {
        parent::__construct();
        $this->progress = new Progress();
        $this->video = new Video();
    }

    #[ORM\PrePersist]
    public function prePersist(): void
    {
        $this->setCreatedAt(new DateTime());
        $this->setUpdatedAt(new DateTime());
    }

    #[ORM\PreUpdate]
    public function preUpdate(): void
    {
        $this->setUpdatedAt(new DateTime());
    }
}

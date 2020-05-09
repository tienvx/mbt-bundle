<?php

namespace Tienvx\Bundle\MbtBundle\Event;

use Symfony\Contracts\EventDispatcher\Event;
use Tienvx\Bundle\MbtBundle\Model\SubjectInterface;

class SubjectInitEvent extends Event
{
    public const NAME = 'mbtbundle.subject.init';

    /**
     * @var SubjectInterface
     */
    protected $subject;

    /**
     * @var bool
     */
    protected $trying;

    public function __construct(SubjectInterface $subject, bool $trying = false)
    {
        $this->subject = $subject;
        $this->trying = $trying;
    }

    public function getSubject(): SubjectInterface
    {
        return $this->subject;
    }

    public function isTrying(): bool
    {
        return $this->trying;
    }
}

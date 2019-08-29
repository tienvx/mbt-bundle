<?php

namespace Tienvx\Bundle\MbtBundle\Reporter;

use Exception;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\MailerInterface;
use Tienvx\Bundle\MbtBundle\Entity\Bug;
use Tienvx\Bundle\MbtBundle\Subject\SubjectManager;

class EmailReporter implements ReporterInterface
{
    /**
     * @var MailerInterface
     */
    protected $mailer;

    /**
     * @var SubjectManager
     */
    protected $subjectManager;

    /**
     * @var string
     */
    protected $emailFrom = '';

    /**
     * @var string
     */
    protected $emailTo = '';

    /**
     * @var string
     */
    protected $emailSubject = 'A new bug found!';

    public function __construct(MailerInterface $mailer, SubjectManager $subjectManager)
    {
        $this->mailer = $mailer;
        $this->subjectManager = $subjectManager;
    }

    public static function getName(): string
    {
        return 'email';
    }

    public function getLabel(): string
    {
        return 'Email';
    }

    public static function support(): bool
    {
        return interface_exists('Symfony\Component\Mailer\MailerInterface') &&
            class_exists('Symfony\Bridge\Twig\Mime\TemplatedEmail');
    }

    public function setEmailFrom(string $emailFrom)
    {
        $this->emailFrom = $emailFrom;
    }

    public function setEmailTo(string $emailTo)
    {
        $this->emailTo = $emailTo;
    }

    public function setEmailSubject(string $emailSubject)
    {
        $this->emailSubject = $emailSubject;
    }

    /**
     * @param Bug $bug
     *
     * @throws Exception
     */
    public function report(Bug $bug)
    {
        if (!class_exists('Symfony\Bridge\Twig\Mime\TemplatedEmail')) {
            return;
        }

        if (empty($this->emailTo) || empty($this->emailFrom) || empty($this->mailer)) {
            return;
        }

        $model = $bug->getModel()->getName();
        $subject = $this->subjectManager->createSubject($model);

        $steps = [];
        foreach ($bug->getSteps() as $index => $step) {
            $steps[] = [
                $index,
                $step->getTransition(),
                $step->getData()->serialize(),
                implode(',', $step->getPlaces()),
                $subject->getScreenshotUrl($bug->getId(), $index),
            ];
        }

        $email = (new TemplatedEmail())
            ->from($this->emailFrom)
            ->to($this->emailTo)
            ->subject($this->emailSubject)
            ->htmlTemplate('@TienvxMbt/reporters/email/report.html.twig')
            ->context([
                'id' => $bug->getId(),
                'title' => $bug->getTitle(),
                'bugMessage' => $bug->getBugMessage(),
                'steps' => $steps,
            ]);

        $this->mailer->send($email);
    }
}

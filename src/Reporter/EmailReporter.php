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

    public function setEmailFrom(string $emailFrom): void
    {
        $this->emailFrom = $emailFrom;
    }

    public function setEmailTo(string $emailTo): void
    {
        $this->emailTo = $emailTo;
    }

    public function setEmailSubject(string $emailSubject): void
    {
        $this->emailSubject = $emailSubject;
    }

    /**
     * @throws Exception
     */
    public function report(Bug $bug): void
    {
        if (!class_exists('Symfony\Bridge\Twig\Mime\TemplatedEmail')) {
            return;
        }

        if ('' === $this->emailTo || '' === $this->emailFrom) {
            return;
        }

        $this->sendEmail($bug);
    }

    protected function formatSteps(Bug $bug): array
    {
        $model = $bug->getModel()->getName();
        $subject = $this->subjectManager->create($model);

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

        return $steps;
    }

    protected function sendEmail(Bug $bug): void
    {
        $email = (new TemplatedEmail())
            ->from($this->emailFrom)
            ->to($this->emailTo)
            ->subject($this->emailSubject)
            ->htmlTemplate('@TienvxMbt/reporters/email/report.html.twig')
            ->context([
                'id' => $bug->getId(),
                'title' => $bug->getTitle(),
                'bugMessage' => $bug->getBugMessage(),
                'steps' => $this->formatSteps($bug),
            ]);

        $this->mailer->send($email);
    }
}

<?php

namespace Tienvx\Bundle\MbtBundle\Reporter;

use Exception;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\MailerInterface;
use Tienvx\Bundle\MbtBundle\Entity\Bug;
use Tienvx\Bundle\MbtBundle\Helper\TableHelper;
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
        return class_exists('Symfony\Component\Mailer\MailerInterface') &&
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

        if (empty($this->emailTo) || empty($this->emailFrom) || empty($this->mailer) || empty($this->twig)) {
            return;
        }

        $path = $bug->getPath();
        $model = $bug->getTask()->getModel()->getName();
        $subject = $this->subjectManager->createSubject($model);

        $steps = [];
        foreach ($path->getSteps() as $index => $step) {
            $steps[] = [
                $index + 1,
                $step[0],
                json_encode($step[1]),
                implode(',', $step[2]),
                $subject->getScreenshotUrl($bug->getId(), $index),
            ];
        }

        $email = (new TemplatedEmail())
            ->from($this->emailFrom)
            ->to($this->emailTo)
            ->subject('New bug found!')
            ->textTemplate('reporters/email/report.txt.twig')
            ->htmlTemplate('reporters/email/report.html.twig')
            ->context([
                'id' => $bug->getId(),
                'task' => $bug->getTask()->getTitle(),
                'title' => $bug->getTitle(),
                'bugMessage' => $bug->getBugMessage(),
                'steps' => $steps,
                'tableSteps' => TableHelper::render($bug->getPath()),
            ]);

        $this->mailer->send($email);
    }
}

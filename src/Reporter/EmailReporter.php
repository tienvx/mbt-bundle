<?php

namespace Tienvx\Bundle\MbtBundle\Reporter;

use Exception;
use Tienvx\Bundle\MbtBundle\Entity\Bug;
use Tienvx\Bundle\MbtBundle\Helper\TableHelper;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Tienvx\Bundle\MbtBundle\Subject\SubjectManager;
use Twig\Environment as Twig;

class EmailReporter implements ReporterInterface
{
    /**
     * @var MailerInterface
     */
    protected $mailer;

    /**
     * @var Twig
     */
    protected $twig;

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

    public function __construct(MailerInterface $mailer, Twig $twig, SubjectManager $subjectManager)
    {
        $this->mailer = $mailer;
        $this->twig = $twig;
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
            class_exists('Twig\Environment') &&
            class_exists('Symfony\Component\Mime\Email');
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
        if (!class_exists('Symfony\Component\Mime\Email')) {
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

        $email = (new Email())
            ->from($this->emailFrom)
            ->to($this->emailTo)
            ->subject('New bug found')
            ->text($this->twig->render('reporters/email/report.txt.twig', [
                'id' => $bug->getId(),
                'task' => $bug->getTask()->getTitle(),
                'title' => $bug->getTitle(),
                'bugMessage' => $bug->getBugMessage(),
                'steps' => TableHelper::render($bug->getPath()),
            ]))
            ->html($this->twig->render('reporters/email/report.html.twig', [
                'id' => $bug->getId(),
                'task' => $bug->getTask()->getTitle(),
                'title' => $bug->getTitle(),
                'bugMessage' => $bug->getBugMessage(),
                'steps' => $steps,
            ]))
        ;

        $this->mailer->send($email);
    }
}

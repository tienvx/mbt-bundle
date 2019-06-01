<?php

namespace Tienvx\Bundle\MbtBundle\Reporter;

use Exception;
use Tienvx\Bundle\MbtBundle\Entity\Bug;
use Tienvx\Bundle\MbtBundle\Graph\Path;
use Tienvx\Bundle\MbtBundle\Helper\TableHelper;
use Swift_Mailer;
use Tienvx\Bundle\MbtBundle\Subject\SubjectManager;
use Twig\Environment;

class EmailReporter implements ReporterInterface
{
    /**
     * @var Swift_Mailer
     */
    protected $mailer;

    /**
     * @var Environment
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

    public function __construct(Swift_Mailer $mailer, Environment $twig, SubjectManager $subjectManager)
    {
        $this->mailer = $mailer;
        $this->twig = $twig;
        $this->subjectManager = $subjectManager;
    }

    public static function getName(): string
    {
        return 'email';
    }

    public static function support(): bool
    {
        return class_exists('Swift_Message');
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
        if (!class_exists('Swift_Message')) {
            return;
        }

        if (empty($this->emailTo) || empty($this->emailFrom) || empty($this->mailer) || empty($this->twig)) {
            return;
        }

        $path = Path::unserialize($bug->getPath());
        $model = $bug->getTask()->getModel();
        $subject = $this->subjectManager->createSubject($model);

        $steps = [];
        foreach ($path as $index => $step) {
            $steps[] = [
                $index + 1,
                $step[0],
                json_encode($step[1]),
                implode(',', $step[2]),
                $subject->getScreenshotUrl($bug->getId(), $index),
            ];
        }

        $message = (new \Swift_Message($bug->getTitle()))
            ->setFrom($this->emailFrom)
            ->setTo($this->emailTo)
            ->setBody(
                $this->twig->render('email-report.html.twig', [
                    'id' => $bug->getId(),
                    'task' => $bug->getTask()->getTitle(),
                    'title' => $bug->getTitle(),
                    'bugMessage' => $bug->getBugMessage(),
                    'steps' => $steps,
                ]),
                'text/html'
            )
            ->addPart(
                $this->twig->render('email-report.txt.twig', [
                    'id' => $bug->getId(),
                    'task' => $bug->getTask()->getTitle(),
                    'title' => $bug->getTitle(),
                    'bugMessage' => $bug->getBugMessage(),
                    'steps' => TableHelper::render(Path::unserialize($bug->getPath())),
                ]),
                'text/plain'
            )
        ;

        $this->mailer->send($message);
    }
}

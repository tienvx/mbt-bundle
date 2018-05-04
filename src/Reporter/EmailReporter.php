<?php

namespace Tienvx\Bundle\MbtBundle\Reporter;

use Symfony\Component\Workflow\Registry;
use Swift_Mailer;
use Tienvx\Bundle\MbtBundle\Entity\Bug;
use Tienvx\Bundle\MbtBundle\Graph\Path;
use Tienvx\Bundle\MbtBundle\Service\GraphBuilder;
use Tienvx\Bundle\MbtBundle\Service\ModelRegistry;
use Twig\Environment as Twig;

class EmailReporter implements ReporterInterface
{
    /** @var Swift_Mailer */
    protected $mailer;
    /** @var Twig */
    protected $twig;
    protected $modelRegistry;
    protected $graphBuilder;
    protected $workflows;
    protected $from;
    protected $to;

    public function __construct(
        ModelRegistry $modelRegistry,
        GraphBuilder $graphBuilder,
        Registry $workflows)
    {
        $this->modelRegistry = $modelRegistry;
        $this->graphBuilder = $graphBuilder;
        $this->workflows = $workflows;
    }

    public function setFrom($from)
    {
        $this->from = $from;
    }

    public function setTo($to)
    {
        $this->to = $to;
    }

    public function setMailer(Swift_Mailer $mailer)
    {
        $this->mailer = $mailer;
    }

    public function setTwig(Twig $twig)
    {
        $this->twig = $twig;
    }

    /**
     * Send email about the bug.
     *
     * @param Bug $bug
     *
     * @throws \Twig_Error_Loader
     * @throws \Twig_Error_Runtime
     * @throws \Twig_Error_Syntax
     * @throws \Exception
     */
    public function report(Bug $bug)
    {
        if (!$this->mailer) {
            throw new \Exception('Need to install symfony/swiftmailer-bundle package to send email');
        }
        if (!$this->twig) {
            throw new \Exception('Need to install symfony/twig-bundle package to send email');
        }
        $this->mailer->send(
            (new \Swift_Message($bug->getTitle()))
                ->setTo($this->to)
                ->setFrom($this->from)
                ->setBody(
                    $this->twig->render(
                        '@TienvxMbt/emails/bug.html.twig',
                        [
                            'id' => $bug->getId(),
                            'task' => $bug->getTask()->getTitle(),
                            'message' => $bug->getMessage(),
                            'steps' => $this->buildSteps($bug),
                            'status' => $bug->getStatus(),
                        ]
                    ),
                    'text/html'
                )
        );
    }

    /**
     * @param Bug $bug
     * @return array
     * @throws \Exception
     */
    protected function buildSteps(Bug $bug): array
    {
        $model = $bug->getTask()->getModel();
        $workflowMetadata = $this->modelRegistry->getModel($model);
        $subject = $workflowMetadata['subject'];
        $workflow = $this->workflows->get(new $subject(), $model);
        $graph = $this->graphBuilder->build($workflow->getDefinition());
        $path = Path::fromSteps($bug->getSteps(), $graph);

        $steps = [];
        foreach ($path->getEdges() as $index => $edge) {
            $steps[] = [
                'step' => $index + 1,
                'action' => $edge->getAttribute('label'),
                'data' => json_encode($path->hasDataAt($index) ? $path->getDataAt($index) : []),
            ];
        }
        return $steps;
    }

    public static function getName()
    {
        return 'email';
    }
}

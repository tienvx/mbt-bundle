<?php

namespace Tienvx\Bundle\MbtBundle\Reporter;

use Symfony\Component\Workflow\Registry;
use Tienvx\Bundle\MbtBundle\Entity\Bug;
use Tienvx\Bundle\MbtBundle\Graph\Path;
use Tienvx\Bundle\MbtBundle\Service\GraphBuilder;
use Tienvx\Bundle\MbtBundle\Service\ModelRegistry;
use Twig\Environment as Twig;

class EmailReporter implements ReporterInterface
{
    protected $mailer;
    protected $twig;
    protected $modelRegistry;
    protected $graphBuilder;
    protected $workflows;
    protected $sendFrom;
    protected $sendTo;

    public function __construct(
        \Swift_Mailer $mailer,
        Twig $twig,
        ModelRegistry $modelRegistry,
        GraphBuilder $graphBuilder,
        Registry $workflows,
        string $sendFrom,
        string $sendTo)
    {
        $this->mailer = $mailer;
        $this->twig = $twig;
        $this->modelRegistry = $modelRegistry;
        $this->graphBuilder = $graphBuilder;
        $this->workflows = $workflows;
        $this->sendFrom = $sendFrom;
        $this->sendTo = $sendTo;
    }

    public function report(Bug $bug)
    {
        $this->mailer->send(
            (new \Swift_Message($bug->getTitle()))
                ->setTo($this->sendTo)
                ->setFrom($this->sendFrom)
                ->setBody(
                    $this->twig->render(
                        'emails/bug.html.twig',
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
                'data' => json_encode($path->getDataAt($index)),
            ];
        }
        return $steps;
    }

    public static function getName()
    {
        return 'email';
    }
}

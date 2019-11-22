<?php

namespace Tienvx\Bundle\MbtBundle\Reporter;

use Exception;
use Tienvx\Bundle\MbtBundle\Entity\Bug;

class SlackReporter implements ReporterInterface
{
    /**
     * @var string
     */
    protected $slackHookUrl = '';

    /**
     * @var string
     */
    protected $slackFrom = '';

    /**
     * @var string
     */
    protected $slackTo = '';

    /**
     * @var string
     */
    protected $slackMessage = '';

    public static function getName(): string
    {
        return 'slack';
    }

    public function getLabel(): string
    {
        return 'Slack';
    }

    public static function support(): bool
    {
        return class_exists('Maknz\Slack\Client');
    }

    public function setSlackHookUrl(string $slackHookUrl): void
    {
        $this->slackHookUrl = $slackHookUrl;
    }

    public function setSlackFrom(string $slackFrom): void
    {
        $this->slackFrom = $slackFrom;
    }

    public function setSlackTo(string $slackTo): void
    {
        $this->slackTo = $slackTo;
    }

    public function setSlackMessage(string $slackMessage): void
    {
        $this->slackMessage = $slackMessage;
    }

    /**
     * @throws Exception
     */
    public function report(Bug $bug): void
    {
        if (!class_exists('Maknz\Slack\Client')) {
            return;
        }

        if ('' === $this->slackTo || '' === $this->slackHookUrl) {
            return;
        }

        $this->sendMessage($bug);
    }

    protected function sendMessage(Bug $bug): void
    {
        $client = new \Maknz\Slack\Client($this->slackHookUrl);

        $client
            ->from($this->slackFrom)
            ->to($this->slackTo)
            ->attach($this->getAttachment($bug))
            ->send($this->slackMessage);
    }

    protected function getAttachment(Bug $bug): array
    {
        return [
            'fallback' => $bug->getBugMessage(),
            'text' => $bug->getTitle(),
            'color' => 'danger',
            'fields' => [
                [
                    'title' => 'ID',
                    'value' => $bug->getId(),
                ],
                [
                    'title' => 'Bug Message',
                    'value' => $bug->getBugMessage(),
                ],
                [
                    'title' => 'Number of Steps',
                    'value' => $bug->getSteps()->getLength(),
                ],
            ],
        ];
    }
}

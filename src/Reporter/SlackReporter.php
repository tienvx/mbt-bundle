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

    public function setSlackHookUrl(string $slackHookUrl)
    {
        $this->slackHookUrl = $slackHookUrl;
    }

    public function setSlackFrom(string $slackFrom)
    {
        $this->slackFrom = $slackFrom;
    }

    public function setSlackTo(string $slackTo)
    {
        $this->slackTo = $slackTo;
    }

    public function setSlackMessage(string $slackMessage)
    {
        $this->slackMessage = $slackMessage;
    }

    /**
     * @param Bug $bug
     *
     * @throws Exception
     */
    public function report(Bug $bug)
    {
        if (!class_exists('Maknz\Slack\Client')) {
            return;
        }

        if (empty($this->slackTo) || empty($this->slackHookUrl)) {
            return;
        }

        $client = new \Maknz\Slack\Client($this->slackHookUrl);

        $client
            ->from($this->slackFrom)
            ->to($this->slackTo)
            ->attach([
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
            ])
            ->send($this->slackMessage);
    }
}

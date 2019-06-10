<?php

namespace Tienvx\Bundle\MbtBundle\Reporter;

use Exception;
use Tienvx\Bundle\MbtBundle\Entity\Bug;
use Tienvx\Bundle\MbtBundle\Helper\TableHelper;

class SlackReporter implements ReporterInterface
{
    /**
     * @var string
     */
    protected $slackHookUrl = '';

    /**
     * @var string
     */
    protected $slackChannel = '';

    public static function getName(): string
    {
        return 'slack';
    }

    public static function support(): bool
    {
        return class_exists('Maknz\Slack\Client');
    }

    public function setSlackHookUrl(string $slackHookUrl)
    {
        $this->slackHookUrl = $slackHookUrl;
    }

    public function setSlackChannel(string $slackChannel)
    {
        $this->slackChannel = $slackChannel;
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

        if (empty($this->slackChannel) || empty($this->slackHookUrl)) {
            return;
        }

        $client = new \Maknz\Slack\Client($this->slackHookUrl);

        $client->to($this->slackChannel)->attach([
            'fallback' => $bug->getBugMessage(),
            'text' => $bug->getBugMessage(),
            'color' => 'danger',
            'fields' => [
                [
                    'title' => 'ID',
                    'value' => $bug->getId(),
                ],
                [
                    'title' => 'Task',
                    'value' => $bug->getTask()->getTitle(),
                ],
                [
                    'title' => 'Steps',
                    'value' => TableHelper::render($bug->getPath()),
                ],
            ],
        ])->send($bug->getTitle());
    }
}

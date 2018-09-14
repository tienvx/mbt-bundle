<?php

namespace Tienvx\Bundle\MbtBundle\Reporter;

use Tienvx\Bundle\MbtBundle\Entity\Bug;

class SlackReporter extends AbstractReporter
{
    /**
     * @var string
     */
    protected $address;

    /**
     * @var string
     */
    protected $channel;

    /**
     * @var string
     */
    protected $token;

    public function setAddress(string $address)
    {
        $this->address = $address;
    }

    public function setChannel(string $channel)
    {
        $this->channel = $channel;
    }

    public function setToken(string $token)
    {
        $this->token = $token;
    }

    /**
     * Send hipchat message about the bug.
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
        $this->check();

        $text = $this->render($bug);

        $this->client->post(
            sprintf('%s/chat.postMessage', $this->address),
            [
                'json' => [
                    'text' => $text,
                    'token' => $this->token,
                    'channel' => $this->channel
                ]
            ]
        );
    }

    public static function getName()
    {
        return 'slack';
    }
}

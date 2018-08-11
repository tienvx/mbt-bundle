<?php

namespace Tienvx\Bundle\MbtBundle\Reporter;

use GuzzleHttp\Client;
use Tienvx\Bundle\MbtBundle\Entity\Bug;
use Twig\Environment as Twig;

class HipchatReporter extends AbstractReporter
{
    /**
     * @var Client
     */
    protected $client;

    /**
     * @var Twig
     */
    protected $twig;

    /**
     * @var string
     */
    protected $address;

    /**
     * @var string
     */
    protected $room;

    /**
     * @var string
     */
    protected $token;

    /**
     * @var string
     */
    protected $color;

    /**
     * @var bool
     */
    protected $notify;

    /**
     * @var string
     */
    protected $format;

    public function setHipchat(Client $client)
    {
        $this->client = $client;
    }

    public function setTwig(Twig $twig)
    {
        $this->twig = $twig;
    }

    public function setAddress(string $address)
    {
        $this->address = $address;
    }

    public function setRoom(string $room)
    {
        $this->room = $room;
    }

    public function setToken(string $token)
    {
        $this->token = $token;
    }

    public function setColor(string $color)
    {
        $this->color = $color;
    }

    public function setNotify(bool $notify)
    {
        $this->notify = $notify;
    }

    public function setFormat(string $format)
    {
        $this->format = $format;
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
        if (!$this->client) {
            throw new \Exception('Need to install guzzlehttp/guzzle package to send hipchat message');
        }
        if (!$this->twig) {
            throw new \Exception('Need to install symfony/twig-bundle package to send hipchat message');
        }

        $message = $this->twig->render(
            '@TienvxMbt/bug-templates/default.html.twig',
            [
              'id'      => $bug->getId(),
              'task'    => $bug->getTask()->getTitle(),
              'message' => $bug->getBugMessage(),
              'steps'   => $this->buildSteps($bug),
              'status'  => $bug->getStatus(),
            ]
        );

        $this->client->post(
            sprintf('%s/room/%s/notification?auth_token=%s', $this->address, $this->room, $this->token),
            [
                'json' => [
                    'message' => $message,
                    'color' => $this->color,
                    'notify' => $this->notify,
                    'message_format' => $this->format
                ]
            ]
        );
    }

    public static function getName()
    {
        return 'hipchat';
    }
}

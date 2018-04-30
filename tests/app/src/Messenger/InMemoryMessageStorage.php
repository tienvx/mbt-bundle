<?php

namespace Tienvx\Bundle\MbtBundle\Tests\Messenger;

class InMemoryMessageStorage
{
    private $messages = [];

    public function add(array $message)
    {
        $this->messages[] = $message;
    }

    public function getMessages()
    {
        return $this->messages;
    }
}

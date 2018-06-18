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
        $messages = $this->messages;
        $this->messages = [];
        return $messages;
    }

    public function hasMessages()
    {
        return !empty($this->messages);
    }

    public function clearMessages()
    {
        $this->messages = [];
    }
}

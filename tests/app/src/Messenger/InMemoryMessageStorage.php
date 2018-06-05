<?php

namespace Tienvx\Bundle\MbtBundle\Tests\Messenger;

class InMemoryMessageStorage
{
    private $messages = [];

    public function add(string $type, array $message)
    {
        $this->messages[$type][] = $message;
    }

    public function getMessages(string $type)
    {
        $messages = $this->messages[$type] ?? [];
        unset($this->messages[$type]);
        return $messages;
    }
}

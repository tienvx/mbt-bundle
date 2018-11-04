<?php

namespace App\Messenger;

class Connection
{
    /**
     * @var array[]
     */
    private $messages = [];

    public function publish($message): void
    {
        $this->messages[] = $message;
    }

    public function get()
    {
        // Memory transport use the same method with pnz/messenger-filesystem-transport: LIFO (Last In, First Out), not
        // FIFO (First In, First Out).
        //return array_shift($this->messages);
        return array_pop($this->messages);
    }

    public function has()
    {
        return count($this->messages) > 0;
    }

    public function clear()
    {
        $this->messages = [];
    }
}

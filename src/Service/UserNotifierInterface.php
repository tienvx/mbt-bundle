<?php

namespace Tienvx\Bundle\MbtBundle\Service;

use Symfony\Component\Notifier\Recipient\RecipientInterface;

interface UserNotifierInterface
{
    public function getRecipient(int $userId): RecipientInterface;
}

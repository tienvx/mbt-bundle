<?php

namespace Tienvx\Bundle\MbtBundle\Service;

use Symfony\Component\Mime\Address;
use Symfony\Component\Notifier\Recipient\RecipientInterface;
use Tienvx\Bundle\MbtBundle\Model\BugInterface;

interface NotifyHelperInterface
{
    public function getRecipient(int $userId): RecipientInterface;

    public function getBugUrl(BugInterface $bug): string;

    public function getFromAddress(): Address;
}

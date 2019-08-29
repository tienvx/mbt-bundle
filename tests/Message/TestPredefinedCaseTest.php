<?php

namespace Tienvx\Bundle\MbtBundle\Tests\Message;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Exception;
use Tienvx\Bundle\MbtBundle\Entity\Bug;

class TestPredefinedCaseTest extends MessageTestCase
{
    /**
     * @throws Exception
     */
    public function testExecute()
    {
        /** @var EntityManagerInterface $entityManager */
        $entityManager = self::$container->get(EntityManagerInterface::class);

        $this->runCommand('mbt:predefined-case:test checkout_out_of_stock');
        $this->consumeMessages();

        /** @var EntityRepository $entityRepository */
        $entityRepository = $entityManager->getRepository(Bug::class);
        /** @var Bug[] $bugs */
        $bugs = $entityRepository->findAll();

        $this->assertEquals(1, count($bugs));
        $this->assertEquals('You added an out-of-stock product into cart! Can not checkout', $bugs[0]->getBugMessage());
    }
}

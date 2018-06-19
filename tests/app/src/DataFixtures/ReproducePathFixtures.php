<?php

namespace Tienvx\Bundle\MbtBundle\Tests\DataFixtures;

use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;
use Tienvx\Bundle\MbtBundle\Entity\ReproducePath;
use Tienvx\Bundle\MbtBundle\Entity\Task;

class ReproducePathFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager)
    {
        /** @var Task $task1 */
        $task1 = $this->getReference('task1');
        /** @var Task $task3 */
        $task3 = $this->getReference('task3');

        $reproducePath1 = new ReproducePath();
        $reproducePath1->setBugMessage('Something happen on shopping_cart model');
        $reproducePath1->setSteps('step1 step2 step3');
        $reproducePath1->setLength(3);
        $reproducePath1->setTask($task1);
        $manager->persist($reproducePath1);
        $this->addReference('reproducePath1', $reproducePath1);

        $reproducePath2 = new ReproducePath();
        $reproducePath2->setBugMessage('We found a bug on shopping_cart model');
        $reproducePath2->setSteps('step1 step2 step3 step4 step5');
        $reproducePath2->setLength(5);
        $reproducePath2->setTask($task1);
        $manager->persist($reproducePath2);
        $this->addReference('reproducePath2', $reproducePath2);

        $reproducePath3 = new ReproducePath();
        $reproducePath3->setBugMessage('Weird bug when we test shoping_cart model');
        $reproducePath3->setSteps('step1 step2');
        $reproducePath3->setLength(2);
        $reproducePath3->setTask($task3);
        $manager->persist($reproducePath3);
        $this->addReference('reproducePath3', $reproducePath3);

        $manager->flush();
    }

    public function getDependencies()
    {
        return [
            TaskFixtures::class,
        ];
    }
}

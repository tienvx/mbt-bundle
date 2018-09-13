<?php

namespace Tienvx\Bundle\MbtBundle\Tests\DataFixtures;

use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Tienvx\Bundle\MbtBundle\Entity\Bug;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;
use Tienvx\Bundle\MbtBundle\Entity\Task;

class BugFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager)
    {
        /** @var Task $task1 */
        $task1 = $this->getReference('task1');
        /** @var Task $task2 */
        $task2 = $this->getReference('task2');

        $bug1 = new Bug();
        $bug1->setTitle('Bug 1');
        $bug1->setStatus('unverified');
        $bug1->setBugMessage('Something happen on shopping_cart model');
        $bug1->setPath('step1 step2 step3');
        $bug1->setLength(3);
        $bug1->setTask($task1);
        $manager->persist($bug1);

        $bug2 = new Bug();
        $bug2->setTitle('Bug 2');
        $bug2->setStatus('valid');
        $bug2->setBugMessage('We found a bug on shopping_cart model');
        $bug2->setPath('step1 step2 step3 step4 step5');
        $bug2->setLength(5);
        $bug2->setTask($task1);
        $manager->persist($bug2);

        $bug3 = new Bug();
        $bug3->setTitle('Bug 3');
        $bug3->setStatus('valid');
        $bug3->setBugMessage('Weird bug when we test shoping_cart model');
        $bug3->setPath('step1 step2');
        $bug3->setLength(2);
        $bug3->setTask($task2);
        $manager->persist($bug3);

        $manager->flush();
    }

    public function getDependencies()
    {
        return [
            TaskFixtures::class,
        ];
    }
}

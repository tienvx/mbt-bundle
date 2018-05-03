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
        /** @var Task $task3 */
        $task3 = $this->getReference('task3');

        $bug1 = new Bug();
        $bug1->setTitle('Bug 1');
        $bug1->setMessage('Something happen on shopping_cart model');
        $bug1->setSteps('step1 step2 step3');
        $bug1->setStatus('unverified');
        $bug1->setReporter('email');
        $bug1->setTask($task1);
        $manager->persist($bug1);

        $bug2 = new Bug();
        $bug2->setTitle('Bug 2');
        $bug2->setMessage('Something happen on shopping_cart model');
        $bug2->setSteps('step1 step2 step3 step4 step5');
        $bug2->setStatus('valid');
        $bug2->setReporter('email');
        $bug2->setTask($task1);
        $manager->persist($bug2);

        $bug3 = new Bug();
        $bug3->setTitle('Bug 3');
        $bug3->setMessage('Something happen on shopping_cart model');
        $bug3->setSteps('step1 step2');
        $bug3->setStatus('invalid');
        $bug3->setReporter('email');
        $bug3->setTask($task3);
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

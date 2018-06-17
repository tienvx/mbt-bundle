<?php

namespace Tienvx\Bundle\MbtBundle\Tests\DataFixtures;

use Tienvx\Bundle\MbtBundle\Entity\Task;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;

class TaskFixtures extends Fixture
{
    public function load(ObjectManager $manager)
    {
        $task1 = new Task();
        $task1->setTitle('Task 1');
        $task1->setModel('shopping_cart');
        $task1->setGenerator('random');
        $task1->setStopCondition('max-length');
        $task1->setStopConditionArguments('{"a":"b"}');
        $task1->setReducer('loop');
        $task1->setProgress(0);
        $task1->setStatus('not-started');
        $manager->persist($task1);
        $this->addReference('task1', $task1);

        $task2 = new Task();
        $task2->setTitle('Task 2');
        $task2->setModel('shopping_cart');
        $task2->setGenerator('random');
        $task2->setStopCondition('max-length');
        $task2->setStopConditionArguments('{"a":"b"}');
        $task2->setReducer('binary');
        $task2->setProgress(64);
        $task2->setStatus('in-progress');
        $manager->persist($task2);
        $this->addReference('task2', $task2);

        $task3 = new Task();
        $task3->setTitle('Task 3');
        $task3->setModel('shopping_cart');
        $task3->setGenerator('random');
        $task3->setStopCondition('max-length');
        $task3->setStopConditionArguments('{"a":"b"}');
        $task3->setReducer('greedy');
        $task3->setProgress(100);
        $task3->setStatus('completed');
        $manager->persist($task3);
        $this->addReference('task3', $task3);

        $manager->flush();
    }
}

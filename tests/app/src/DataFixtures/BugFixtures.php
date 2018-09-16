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
        $bug1->setPath('C:34:"Tienvx\Bundle\MbtBundle\Graph\Path":165:{a:3:{i:0;a:3:{i:0;N;i:1;s:11:"transition1";i:2;s:11:"transition2";}i:1;a:3:{i:0;N;i:1;a:0:{}i:2;a:0:{}}i:2;a:3:{i:0;s:6:"place1";i:1;s:6:"place2";i:2;s:6:"place3";}}}');
        $bug1->setLength(3);
        $bug1->setTask($task1);
        $manager->persist($bug1);

        $bug2 = new Bug();
        $bug2->setTitle('Bug 2');
        $bug2->setStatus('valid');
        $bug2->setBugMessage('We found a bug on shopping_cart model');
        $bug2->setPath('C:34:"Tienvx\Bundle\MbtBundle\Graph\Path":265:{a:3:{i:0;a:5:{i:0;N;i:1;s:11:"transition1";i:2;s:11:"transition2";i:3;s:11:"transition3";i:4;s:11:"transition4";}i:1;a:5:{i:0;N;i:1;a:0:{}i:2;a:0:{}i:3;a:0:{}i:4;a:0:{}}i:2;a:5:{i:0;s:6:"place1";i:1;s:6:"place2";i:2;s:6:"place3";i:3;s:6:"place4";i:4;s:6:"place5";}}}');
        $bug2->setLength(5);
        $bug2->setTask($task1);
        $manager->persist($bug2);

        $bug3 = new Bug();
        $bug3->setTitle('Bug 3');
        $bug3->setStatus('valid');
        $bug3->setBugMessage('Weird bug when we test shoping_cart model');
        $bug3->setPath('C:34:"Tienvx\Bundle\MbtBundle\Graph\Path":115:{a:3:{i:0;a:2:{i:0;N;i:1;s:11:"transition1";}i:1;a:2:{i:0;N;i:1;a:0:{}}i:2;a:2:{i:0;s:6:"place1";i:1;s:6:"place2";}}}');
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

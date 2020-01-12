<?php

namespace App\DataFixtures;

use App\Entity\Task;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;

class TaskFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager)
    {
        $faker = Factory::create();

        $task = new Task();

        $task
            ->setTitle('testTask')
            ->setContent('test Task Content');

        $manager->persist($task);

        for ($i = 0; $i < 10; $i++) {
            $task = new Task();

            $task
                ->setCreatedAt($faker->dateTimeThisDecade())
                ->setTitle($faker->words(rand(1, 5), true))
                ->setContent($faker->paragraph(rand(3, 20)))
                ->setIsDone($faker->boolean($chanceOfGettingTrue = 50));

            $manager->persist($task);
        }

        for ($i = 0; $i < 5; $i++) {
            $task = new Task();

            $task
                ->setCreatedAt($faker->dateTimeThisDecade())
                ->setTitle('task'.$i)
                ->setContent($faker->paragraph(rand(3, 20)))
                ->setIsDone($faker->boolean($chanceOfGettingTrue = 50))
                ->setUser($this->getReference(UserFixtures::TASK_USER_REFERENCE));

            $manager->persist($task);
        }


        $manager->flush();
    }

    public function getDependencies()
    {
        return array(
            UserFixtures::class,
        );
    }
}

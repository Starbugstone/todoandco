<?php

namespace App\DataFixtures;

use App\Entity\Task;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;
use Faker\Factory;

class TaskFixtures extends Fixture
{
    public function load(ObjectManager $manager)
    {
        $faker = Factory::create();

        for($i=0; $i<10; $i++){
            $task = new Task();

            $task
                ->setCreatedAt($faker->dateTimeThisDecade())
                ->setTitle($faker->words(rand(1,5), true))
                ->setContent($faker->paragraph(rand(3,20)))
                ->setIsDone($faker->boolean($chanceOfGettingTrue = 50));

            $manager->persist($task);
        }


        $manager->flush();
    }
}

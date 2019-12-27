<?php

namespace App\DataFixtures;

use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class UserFixtures extends Fixture
{
    public const TASK_USER_REFERENCE = 'task-user';
    /**
     * @var UserPasswordEncoderInterface
     */
    private $passwordEncoder;

    public function __construct(UserPasswordEncoderInterface $passwordEncoder)
    {
        $this->passwordEncoder = $passwordEncoder;
    }

    public function load(ObjectManager $manager)
    {
        $user = new User();
        $encoded = $this->passwordEncoder->encodePassword($user, 'password');

        $user
            ->setUsername('admin')
            ->setRoles(['ROLE_ADMIN'])
            ->setPassword($encoded)
            ->setEmail('admin@localhost.com');

        $manager->persist($user);

        //variable random users
        for ($i = 0; $i < 10; $i++) {
            $user = new User();
            $encoded = $this->passwordEncoder->encodePassword($user, 'password');
            $user
                ->setUserName('user' . $i)
                ->setPassword($encoded)
                ->setEmail('user' . $i . '@localhost.com');

            $manager->persist($user);
        }

        //user that will have tasks
        $taskUser = new User();
        $encoded = $this->passwordEncoder->encodePassword($taskUser, 'password');
        $taskUser
            ->setUserName('taskuser')
            ->setPassword($encoded)
            ->setEmail('taskuser@localhost.com');

        $manager->persist($taskUser);

        $manager->flush();

        $this->addReference(self::TASK_USER_REFERENCE, $taskUser);
    }
}

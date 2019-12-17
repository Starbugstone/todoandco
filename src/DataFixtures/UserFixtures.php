<?php

namespace App\DataFixtures;

use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class UserFixtures extends Fixture
{

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

        for ($i = 0; $i < 10; $i++) {
            $user = new User();
            $encoded = $this->passwordEncoder->encodePassword($user, 'password');
            $user
                ->setUserName('user' . $i)
                ->setPassword($encoded)
                ->setEmail('user' . $i . '@localhost.com');

            $manager->persist($user);
        }

        $manager->flush();
    }
}

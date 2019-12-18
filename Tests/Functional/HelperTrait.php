<?php


namespace App\Tests\Functional;


use App\Entity\Task;
use App\Entity\User;
use Doctrine\ORM\EntityManager;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;

Trait HelperTrait
{

    public function getUser(EntityManager $entityManager, string $username = 'user1'): User
    {
        /** @var User $user */
        $user = $entityManager->getRepository(User::class)->findOneBy(['username' => $username]);
        if (!$user) {
            throw new \Exception($username . ' not found in database, did you load the fixtures ?');
        }
        return $user;
    }

    public function getTask(EntityManager $entityManager, string $title = 'testTask'): Task
    {
        /** @var Task $task */
        $task = $entityManager->getRepository(User::class)->findOneBy(['title' => $title]);
        if (!$task) {
            throw new \Exception($title . ' not found in database, did you load the fixtures ?');
        }
        return $task;
    }

    //login, default to user1
    public function loginClient(KernelBrowser $client, $username = 'user1', $password = 'password'): KernelBrowser
    {
        $crawler = $client->request('GET', '/login');

        //filling out the login form
        $form = $crawler->selectButton('Se connecter')->form();
        $form['username'] = $username;
        $form['password'] = $password;
        $crawler = $client->submit($form);
        $client->followRedirect();
        return $client;
    }
}
<?php


namespace App\EventListener;


use App\Entity\AnonymousUser;
use App\Entity\Task;
use App\Entity\User;
use Psr\Log\LoggerInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class DeleteTask
{

    /**
     * @var TokenStorageInterface
     */
    private $tokenStorage;
    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(TokenStorageInterface $tokenStorage, LoggerInterface $logger)
    {
        $this->tokenStorage = $tokenStorage;
        $this->logger = $logger;
    }


    //check if we can delete the task
    public function preRemove(Task $task)
    {
        $token = $this->tokenStorage->getToken();
        if (!$token) {
            $this->logger->error('Task attempted to delete with non logged on user');
            throw new \Exception('impossible to delete task, you are not logged in');
        }

        $user = $token->getUser();
        if (!$user instanceof User) {
            $this->logger->error('Task attempted to delete but the user returned was not of type user');
            throw new \Exception('impossible to delete task, your login is incorrect');
        }

        if (!($task->getUser() === $user || ($task->getUser() instanceof AnonymousUser && $user->isAdmin()))) {
            throw new \Exception('You are no allowed to delete the task');
        }
    }
}
<?php


namespace App\EventListener;


use App\Entity\Task;
use App\Entity\User;
use Psr\Log\LoggerInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class AddUserToNewTask
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

    //add the logged on user to the task on creation
    public function PrePersist(Task $task)
    {
        $token = $this->tokenStorage->getToken();
        if (!$token) {
            $this->logger->error('Task created with non logged on user');
            return;
        }

        $user = $token->getUser();
        if (!$user instanceof User) {
            $this->logger->error('Task created but the user returned was not of type user');
            return;
        }

        $task->setUser($user);
    }
}
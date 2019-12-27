<?php


namespace App\EventListener;


use App\Entity\Task;
use App\Entity\User;
use Doctrine\Persistence\Event\LifecycleEventArgs;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class AddUserToNewTask
{

    /**
     * @var User $user
     */
    private $user;

    public function __construct(TokenStorageInterface $tokenStorage)
    {
        $user = $tokenStorage->getToken()->getUser();
        $this->user = $user;
    }

    //add the logged on user to the task on creation
    public function PrePersist(Task $task, LifecycleEventArgs $event)
    {
        if ($this->user === null){
            throw new \Exception('registering a task with an anonymous user');
        }
        $task->setUser($this->user);
    }
}
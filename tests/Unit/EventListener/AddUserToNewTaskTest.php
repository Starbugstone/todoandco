<?php

namespace App\Tests\Unit\EventListener;

use App\Entity\Task;
use App\Entity\User;
use App\EventListener\AddUserToNewTask;
use Doctrine\Persistence\Event\LifecycleEventArgs;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;



class AddUserToNewTaskTest extends TestCase
{

    public function testAddNewUserToTask()
    {
        $user = new User();
        $task = new Task();

        $tokenStorageMock = $this->createMock(TokenStorageInterface::class);
        $tokenInterfaceMock = $this->createMock(TokenInterface::class);

        $tokenStorageMock->expects($this->once())
            ->method('getToken')
            ->willReturn($tokenInterfaceMock);
        $tokenInterfaceMock->expects($this->once())
            ->method('getUser')
            ->willReturn($user);

        $addUserToNewTask = new AddUserToNewTask($tokenStorageMock);

        $addUserToNewTask->PrePersist($task);
        $this->assertEquals($user, $task->getUser());

    }

    public function testAddNewUserToTaskNoUser()
    {
        $task = new Task();

        $tokenStorageMock = $this->createMock(TokenStorageInterface::class);
        $tokenInterfaceMock = $this->createMock(TokenInterface::class);

        $tokenStorageMock->expects($this->once())
            ->method('getToken')
            ->willReturn($tokenInterfaceMock);
        $tokenInterfaceMock->expects($this->once())
            ->method('getUser')
            ->willReturn(null);

        $addUserToNewTask = new AddUserToNewTask($tokenStorageMock);

        $addUserToNewTask->PrePersist($task);
        $this->assertEquals('Anonymous', $task->getUser()->getUsername());
    }

    public function testAddNewUserToTaskBadUser()
    {
        $user = 'test';
        $task = new Task();

        $tokenStorageMock = $this->createMock(TokenStorageInterface::class);
        $tokenInterfaceMock = $this->createMock(TokenInterface::class);

        $tokenStorageMock->expects($this->once())
            ->method('getToken')
            ->willReturn($tokenInterfaceMock);
        $tokenInterfaceMock->expects($this->once())
            ->method('getUser')
            ->willReturn($user);

        $addUserToNewTask = new AddUserToNewTask($tokenStorageMock);

        $addUserToNewTask->PrePersist($task);
        $this->assertEquals('Anonymous', $task->getUser()->getUsername());
    }
}
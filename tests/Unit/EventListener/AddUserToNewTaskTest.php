<?php

namespace App\Tests\Unit\EventListener;

use App\Entity\AnonymousUser;
use App\Entity\Task;
use App\Entity\User;
use App\EventListener\AddUserToNewTask;
use Doctrine\Persistence\Event\LifecycleEventArgs;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
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
        $loggerInterfaceMock = $this->createMock(LoggerInterface::class);

        $tokenStorageMock->expects($this->once())
            ->method('getToken')
            ->willReturn($tokenInterfaceMock);
        $tokenInterfaceMock->expects($this->once())
            ->method('getUser')
            ->willReturn($user);

        $addUserToNewTask = new AddUserToNewTask($tokenStorageMock, $loggerInterfaceMock);

        $addUserToNewTask->PrePersist($task);
        $this->assertEquals($user, $task->getUser());

    }

    public function testAddNewUserToTaskNoUser()
    {
        $task = new Task();

        $tokenStorageMock = $this->createMock(TokenStorageInterface::class);
        $tokenInterfaceMock = $this->createMock(TokenInterface::class);
        $loggerInterfaceMock = $this->createMock(LoggerInterface::class);

        $tokenStorageMock->expects($this->once())
            ->method('getToken')
            ->willReturn(null);
        $loggerInterfaceMock->expects($this->once())
            ->method('error');

        $addUserToNewTask = new AddUserToNewTask($tokenStorageMock, $loggerInterfaceMock);

        $addUserToNewTask->PrePersist($task);
        $this->assertEquals(AnonymousUser::ANONYMOUS_USERNAME, $task->getUser()->getUsername());
    }

    public function testAddNewUserToTaskBadUser()
    {
        $user = 'test';
        $task = new Task();

        $tokenStorageMock = $this->createMock(TokenStorageInterface::class);
        $tokenInterfaceMock = $this->createMock(TokenInterface::class);
        $loggerInterfaceMock = $this->createMock(LoggerInterface::class);

        $tokenStorageMock->expects($this->once())
            ->method('getToken')
            ->willReturn($tokenInterfaceMock);
        $tokenInterfaceMock->expects($this->once())
            ->method('getUser')
            ->willReturn($user);
        $loggerInterfaceMock->expects($this->once())
            ->method('error');

        $addUserToNewTask = new AddUserToNewTask($tokenStorageMock, $loggerInterfaceMock);

        $addUserToNewTask->PrePersist($task);
        $this->assertEquals(AnonymousUser::ANONYMOUS_USERNAME, $task->getUser()->getUsername());
    }
}
<?php


namespace App\Tests\Unit\EventListener;


use App\Entity\Task;
use App\Entity\User;
use App\EventListener\DeleteTask;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

class DeleteTaskTest extends TestCase
{
    public function testBadToken()
    {
        $tokenStorageMock = $this->createMock(TokenStorageInterface::class);
        $tokenInterfaceMock = $this->createMock(TokenInterface::class);
        $loggerInterfaceMock = $this->createMock(LoggerInterface::class);

        $tokenStorageMock->expects($this->once())
            ->method('getToken')
            ->willReturn(null);


        $deleteTask = new DeleteTask($tokenStorageMock, $loggerInterfaceMock);

        $task = new Task();
        $this->expectException(\Exception::class);
        $deleteTask->preRemove($task);
    }

    public function testBadUser()
    {
        $tokenStorageMock = $this->createMock(TokenStorageInterface::class);
        $tokenInterfaceMock = $this->createMock(TokenInterface::class);
        $loggerInterfaceMock = $this->createMock(LoggerInterface::class);

        $tokenStorageMock->expects($this->once())
            ->method('getToken')
            ->willReturn($tokenInterfaceMock);
        $tokenInterfaceMock->expects($this->once())
            ->method('getUser')
            ->willReturn(null);


        $deleteTask = new DeleteTask($tokenStorageMock, $loggerInterfaceMock);

        $task = new Task();
        $this->expectException(\Exception::class);
        $deleteTask->preRemove($task);
    }

    public function testAccessDenied()
    {
        $tokenStorageMock = $this->createMock(TokenStorageInterface::class);
        $tokenInterfaceMock = $this->createMock(TokenInterface::class);
        $loggerInterfaceMock = $this->createMock(LoggerInterface::class);

        $tokenStorageMock->expects($this->once())
            ->method('getToken')
            ->willReturn($tokenInterfaceMock);
        $tokenInterfaceMock->expects($this->once())
            ->method('getUser')
            ->willReturn(new User());


        $deleteTask = new DeleteTask($tokenStorageMock, $loggerInterfaceMock);

        $task = new Task();
        $this->expectException(AccessDeniedHttpException::class);
        $deleteTask->preRemove($task);
    }

}
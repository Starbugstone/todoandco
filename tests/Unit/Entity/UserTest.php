<?php

namespace App\Tests\Unit\Entity;

use App\Entity\Task;
use App\Entity\User;
use PHPUnit\Framework\TestCase;

class UserTest extends TestCase
{
    public function testUser()
    {
        $user = new User();
        $this->assertTrue(in_array('ROLE_USER',$user->getRoles()),"ROLE_USER in not defaulted to new user");
        $roles[] = 'ROLE_TEST';
        $user->setRoles($roles);
        $this->assertTrue(in_array('ROLE_USER',$user->getRoles()),"ROLE_USER in not in user after role update");
        $this->assertTrue(in_array('ROLE_TEST',$user->getRoles()),"The SetRoles is not registering properly");
        $this->assertFalse(in_array('NOT_A_ROLE',$user->getRoles()),"The user has a nonexistent role");

        $user->setUsername('HanSolo');
        $user->setEmail('hansolo@shootfirst.com');
        $this->assertEquals('HanSolo', $user->getUsername());
        $this->assertEquals('hansolo@shootfirst.com', $user->getEmail());

        //id should be null as it is not saved to DB
        $this->assertNull($user->getId());

        //testing getters and setters for the password. they are not encoded here so a functional test will be needed
        $user->setPassword('P4ssW0rd');
        $this->assertEquals('P4ssW0rd',$user->getPassword());

        //testing the task / user relationship
        $task = new Task();
        $task->setTitle('TestTask1');
        $task->setContent('TestTask1Content');
        //Adding and removing the task from user
        $user->addTask($task);
        $this->assertCount(1,$user->getTasks());
        $this->assertEquals($task, $user->getTasks()[0]);
        $user->removeTask($task);
        $this->assertCount(0,$user->getTasks());


        //these methods are not used so they don't return anything
        $this->assertNull($user->getSalt());
        $this->assertNull($user->eraseCredentials());





    }
}
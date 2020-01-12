<?php

namespace App\Tests\Unit\Entity;

use App\Entity\AnonymousUser;
use App\Entity\Task;
use App\Entity\User;
use PHPUnit\Framework\TestCase;

class TaskTest extends TestCase
{
    public function testTask()
    {
        $task = new Task();

        // Making sure that we initialise with a valid datetime and not isDone
        $this->assertTrue($task->getCreatedAt() <= new \Datetime());
        $this->assertFalse($task->getIsDone());

        //id should be null as it is not saved to DB
        $this->assertNull($task->getId());

        // testing getters and setters
        $task->setTitle('foo');
        $task->setContent('bar');

        $this->assertEquals('foo', $task->getTitle());
        $this->assertEquals('bar', $task->getContent());

        $now = new \Datetime();
        $task->setCreatedAt($now);
        $this->assertEquals($now, $task->getCreatedAt());

        //making sure that the custom functions for isdone are working
        $task->setIsDone(true);
        $this->assertTrue($task->getIsDone());
        $this->assertTrue($task->isDone());
        $this->assertFalse($task->toggleIsDone());

        //testing the task / user relationship
        $user = new User();
        $user->setUsername('TestUser1');

        //if no user we should return an anonymous user
        $this->assertEquals(AnonymousUser::ANONYMOUS_USERNAME, $task->getUser()->getUsername());
        $task->setUser($user);
        $this->assertEquals($user, $task->getUser());
        $task->setUser(null);
        $this->assertEquals(AnonymousUser::ANONYMOUS_USERNAME, $task->getUser()->getUsername());
    }
}
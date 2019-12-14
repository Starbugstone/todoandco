<?php

use App\Entity\Task;
use PHPUnit\Framework\TestCase;

class TaskTest extends TestCase
{
    public function testTask()
    {
        $task = new Task();

        // Making sure that we initialise with a valid datetime and not isDone
        $this->assertTrue($task->getCreatedAt() <= new \Datetime());
        $this->assertFalse($task->getIsDone());

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
    }
}
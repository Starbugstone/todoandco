<?php


namespace App\Tests\Functional;


use App\Entity\Task;
use Doctrine\ORM\EntityManager;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class TaskTest extends WebTestCase
{
    use HelperTrait;

    /**
     * @var EntityManager
     */
    private $entityManager;

    protected function setUp(): void
    {
        $kernel = self::bootKernel();

        $this->entityManager = $kernel->getContainer()
            ->get('doctrine')
            ->getManager();
        self::ensureKernelShutdown(); //close the kernel now we have the entity manager
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        // doing this is recommended to avoid memory leaks
        $this->entityManager->close();
        $this->entityManager = null;
    }

    public function testCreateTask()
    {
        //logging in as client
        $client = static::createClient();
        $client = $this->loginClient($client);

        //navigate to the creation url
        $crawler = $client->request('GET', '/tasks/create');
        $this->assertEquals(200, $client->getResponse()->getStatusCode(), '/tasks/create did not respond with a 200 code while logged in as user');

        //filling out the form
        $form = $crawler->selectButton('Ajouter')->form();
        $form['task[title]'] = 'MyTestTask';
        $form['task[content]'] = 'lorem ipsup delor';
        $crawler = $client->submit($form);

        //we should redirect to the task list
        $this->assertTrue($client->getResponse()->isRedirect('/tasks'), 'Did not redirect to /tasks after new task creation');

        //checking if the data is correct in database
        $task = $this->getTask($this->entityManager, 'MyTestTask');
        $this->assertNotNull($task, 'Newley created MyTestTask is not present in the database');
        $this->assertEquals('lorem ipsup delor', $task->getContent(), 'the newly created task content is not set correctly');
        $this->assertFalse($task->getIsDone(), 'The newly created task seams to be set as IsDone after creation. This should be set to false');
    }

    public function testEditTask()
    {
        $client = static::createClient();
        $client = $this->loginClient($client);
        $task = $this->getTask($this->entityManager);

        $crawler = $client->request('GET', '/tasks/' . $task->getId() . '/edit');
        $this->assertEquals(200, $client->getResponse()->getStatusCode(), '/tasks/' . $task->getId() . '/edit did not respond with a 200 status code while logged in as a user');

        $form = $crawler->selectButton('Modifier')->form();
        $form['task[title]'] = 'testTaskEdited';
        $form['task[content]'] = 'lorem ipsup delor Sith';
        $crawler = $client->submit($form);
        $this->assertTrue($client->getResponse()->isRedirect('/tasks'), 'Did not redirect to /tasks after a task edit');
        $crawler = $client->followRedirect();
        $crawler = $client->request('GET', '/tasks/' . $task->getId() . '/edit');

        //can't recall database element as it's cached and blocked by test rollback so checking via the form
        $form = $crawler->selectButton('Modifier')->form();
        $this->assertEquals('testTaskEdited', $form['task[title]']->getValue(), 'The edited task title is not correct');
        $this->assertEquals('lorem ipsup delor Sith', $form['task[content]']->getValue(), 'The edited task content is not correct');


    }

    public function testToggleTask()
    {
        $client = static::createClient();
        $client = $this->loginClient($client);
        $task = $this->getTask($this->entityManager);

        //Making sure that our default test task is tagged as not done
        $this->assertFalse($task->isDone(), $task->getTitle() . ' isDone is not set to false');

        //toggle to done
        $crawler = $client->request('GET', '/tasks/' . $task->getId() . '/toggle');
        $this->assertTrue($client->getResponse()->isRedirect('/tasks', '/tasks/' . $task->getId() . '/toggle dod not redirect to /tasks'));
        $crawler = $client->followRedirect();
        $this->assertSelectorTextContains('html div.alert-success>p',
            sprintf('La tâche %s a bien été marquée comme fait.', $task->getTitle()), 'the flash message is not correct after task toggle to done');

        //toggle to not finished
        $crawler = $client->request('GET', '/tasks/' . $task->getId() . '/toggle');
        $this->assertTrue($client->getResponse()->isRedirect('/tasks'));
        $crawler = $client->followRedirect();
        $this->assertSelectorTextContains('html div.alert-success>p',
            sprintf('La tâche %s a bien été marquée comme non terminée.', $task->getTitle()), 'the flash message is not correct after task toggle to not done');
    }

    public function testDeleteTask()
    {
        $client = static::createClient();
        $client = $this->loginClient($client);
        $task = $this->getTask($this->entityManager);

        //delete the task and check the flash message
        $crawler = $client->request('GET', '/tasks/' . $task->getId() . '/delete');
        $this->assertTrue($client->getResponse()->isRedirect('/tasks'), 'Did not redirect to /tasks after task delition');
        $crawler = $client->followRedirect();
        $this->assertSelectorTextContains('html div.alert-success>p', 'La tâche a bien été supprimée.', 'The task deletion flash message is not correct');

        //make sure that the task is no longer in DB
        $task = $this->entityManager->getRepository(Task::class)->findOneBy(['title' => HelperConstants::TEST_TASK]);
        $this->assertNull($task, 'The deleted task is still in the database');
    }

}
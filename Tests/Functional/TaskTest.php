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
        $this->assertEquals(200, $client->getResponse()->getStatusCode());

        //filling out the form
        $form = $crawler->selectButton('Ajouter')->form();
        $form['task[title]'] = 'MyTestTask';
        $form['task[content]'] = 'lorem ipsup delor';
        $crawler = $client->submit($form);

        //we should redirect to the task list
        $this->assertTrue($client->getResponse()->isRedirect('/tasks'));

        //checking if the data is correct in database
        $task = $this->getTask($this->entityManager, 'MyTestTask');
        $this->assertNotNull($task);
        $this->assertEquals('lorem ipsup delor', $task->getContent());
        $this->assertFalse($task->getIsDone());
    }

    public function testEditTask()
    {
        $client = static::createClient();
        $client = $this->loginClient($client);
        $task = $this->getTask($this->entityManager);

        $crawler = $client->request('GET', '/tasks/' . $task->getId() . '/edit');
        $this->assertEquals(200, $client->getResponse()->getStatusCode());

        $form = $crawler->selectButton('Modifier')->form();
        $form['task[title]'] = 'testTaskEdited';
        $form['task[content]'] = 'lorem ipsup delor Sith';
        $crawler = $client->submit($form);
        $this->assertTrue($client->getResponse()->isRedirect('/tasks'));
        $crawler = $client->followRedirect();
        $crawler = $client->request('GET', '/tasks/' . $task->getId() . '/edit');

        //can't recall database element as it's cached and blocked by test rollback so checking via the form
        $form = $crawler->selectButton('Modifier')->form();
        $this->assertEquals('testTaskEdited', $form['task[title]']->getValue());
        $this->assertEquals('lorem ipsup delor Sith', $form['task[content]']->getValue());


    }

    public function testToggleTask()
    {
        $client = static::createClient();
        $client = $this->loginClient($client);
        $task = $this->getTask($this->entityManager);

        //Making sure that our default test task is tagged as not done
        $this->assertFalse($task->isDone());

        //toggle to done
        $crawler = $client->request('GET', '/tasks/' . $task->getId() . '/toggle');
        $this->assertTrue($client->getResponse()->isRedirect('/tasks'));
        $crawler = $client->followRedirect();
        $this->assertSelectorTextContains('html div.alert-success>p',
            sprintf('La tâche %s a bien été marquée comme fait.', $task->getTitle()));

        //toggle to not finished
        $crawler = $client->request('GET', '/tasks/' . $task->getId() . '/toggle');
        $this->assertTrue($client->getResponse()->isRedirect('/tasks'));
        $crawler = $client->followRedirect();
        $this->assertSelectorTextContains('html div.alert-success>p',
            sprintf('La tâche %s a bien été marquée comme non terminée.', $task->getTitle()));
    }

    public function testDeleteTask()
    {
        $client = static::createClient();
        $client = $this->loginClient($client);
        $task = $this->getTask($this->entityManager);

        //delete the task and check the flash message
        $crawler = $client->request('GET', '/tasks/' . $task->getId() . '/delete');
        $this->assertTrue($client->getResponse()->isRedirect('/tasks'));
        $crawler = $client->followRedirect();
        $this->assertSelectorTextContains('html div.alert-success>p','La tâche a bien été supprimée.');

        //make sure that the task is no longer in DB
        $task = $this->entityManager->getRepository(Task::class)->findOneBy(['title' => HelperConstants::TEST_TASK]);
        $this->assertNull($task);
    }

}
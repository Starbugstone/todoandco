<?php


namespace App\Tests\Functional;


use App\Entity\User;
use Doctrine\ORM\EntityManager;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class MyTest extends WebTestCase
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
    //log in as a user and update our password
    public function testEditUserPassword()
    {
        $client = static::createClient();
        $client = $this->loginClient($client);

        /** @var User $user */
        $user = $this->getUser($this->entityManager);
        $crawler = $client->request('GET', '/users/' . $user->getId() . '/edit');
        $this->assertEquals(200, $client->getResponse()->getStatusCode(),
            '/users/' . $user->getId() . '/edit did not respond with a 200 code when logged in as user');

        //checking we have the username in bold for editing
        $this->assertSelectorTextContains('html div>h1',
            'Modifier', 'We did not find \'Modifier\' on the edit page while logged in as user');
        $this->assertSelectorTextContains('html div>h1>strong',
            $user->getUsername(), $user->getUsername() . ' not in bold on the edit page while logged in as user');

        //change password
        $form = $this->userForm($crawler, 'Modifier', 'editedUser', 'pass2', 'pass2', 'user1@localhost.com');
        $crawler = $client->submit($form);
        $this->assertTrue($client->getResponse()->isRedirect('/users'),
            'did not redirect to /users after password update');
        $client->followRedirect();

        //logout and log back in with new password
        $crawler = $client->request('GET', '/logout');
        $client = $this->loginClient($client, 'editedUser', 'pass2');

        //return to editing to check all is ok
        $crawler = $client->request('GET', '/users/' . $user->getId() . '/edit');
        $this->assertEquals(200, $client->getResponse()->getStatusCode(),
            'We could not edit our user after logout and login with new password');

        //checking we have the username in bold for editing
        $this->assertSelectorTextContains('html div>h1',
            'Modifier', 'We did not find \'Modifier\' on the edit page while logged in as user after password update');
        $this->assertSelectorTextContains('html div>h1>strong',
            'editedUser',
            $user->getUsername() . ' not in bold on the edit page while logged in as user after password update');

    }
}
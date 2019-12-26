<?php


namespace App\Tests\Functional;


use App\Entity\User;
use Doctrine\ORM\EntityManager;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\DomCrawler\Crawler;

class UserCrudTest extends WebTestCase
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

    public function testBadUserLogin()
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/login');
        $this->assertEquals(200, $client->getResponse()->getStatusCode());

        //filling out the login form
        $form = $crawler->selectButton('Se connecter')->form();
        $form['username'] = 'aNonExistentUser';
        $form['password'] = 'badPassword';
        $crawler = $client->submit($form);

        //we should be sent back to the login form
        $this->assertTrue($client->getResponse()->isRedirect('/login'));
        $crawler = $client->followRedirect();

        //check that we have a flash
        $this->assertSelectorTextContains('html div.alert-danger', 'Le nom d\'utilisateur est incorrect.');

        //connect with bad password
        $form = $crawler->selectButton('Se connecter')->form();
        $form['username'] = HelperConstants::TEST_USER;
        $form['password'] = 'badPassword';
        $crawler = $client->submit($form);

        $this->assertTrue($client->getResponse()->isRedirect('/login'));
        $crawler = $client->followRedirect();

        $this->assertSelectorTextContains('html div.alert-danger', 'Identifiants invalides.');
    }

    //create user with errors
    public function testCreateUserError()
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/users/create');
        $this->assertEquals(200, $client->getResponse()->getStatusCode());

        //check that same password
        $form = $this->userForm($crawler, 'Ajouter','badUser', 'pass', 'pass2', 'mail@localhost.com');
        $crawler = $client->submit($form);
        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        $this->assertSelectorTextContains('html span.form-error-message',
            'Les deux mots de passe doivent correspondre.');

    }

    //create a good user
    public function testCreateUser()
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/users/create');
        $this->assertEquals(200, $client->getResponse()->getStatusCode());

        $form = $this->userForm($crawler, 'Ajouter','goodUser', 'pass', 'pass', 'mail@localhost.com');
        $crawler = $client->submit($form);

        $this->assertTrue($client->getResponse()->isRedirect('/users'));
        $crawler = $client->followRedirect();


        $user = $this->getUser($this->entityManager, 'goodUser');
        $this->assertNotNull($user);
        $this->assertEquals('mail@localhost.com', $user->getEmail());
    }

    //login as admin and edit a username
    public function testEditUser()
    {
        $client = static::createClient();
        $client = $this->loginAdmin($client);

        /** @var User $user */
        $user = $this->getUser($this->entityManager);
        $crawler = $client->request('GET', '/users/' . $user->getId() . '/edit');
        $this->assertEquals(200, $client->getResponse()->getStatusCode());

        //checking we have the username in bold for editing
        $this->assertSelectorTextContains('html div>h1',
            'Modifier');
        $this->assertSelectorTextContains('html div>h1>strong',
            $user->getUsername());

        $form = $this->userForm($crawler, 'Modifier','editedUser', 'pass', 'pass', 'user1@localhost.com');
        $crawler = $client->submit($form);
        $this->assertTrue($client->getResponse()->isRedirect('/users'));
        $user = $this->getUser($this->entityManager, 'editedUser');
        $this->assertNotNull($user);

        $this->assertEquals('user1@localhost.com', $user->getEmail());
    }

    //log in as a user and update our password
    public function testEditUserPassword()
    {
        $client = static::createClient();
        $client = $this->loginClient($client);

        /** @var User $user */
        $user = $this->getUser($this->entityManager);
        $crawler = $client->request('GET', '/users/' . $user->getId() . '/edit');
        $this->assertEquals(200, $client->getResponse()->getStatusCode());

        //checking we have the username in bold for editing
        $this->assertSelectorTextContains('html div>h1',
            'Modifier');
        $this->assertSelectorTextContains('html div>h1>strong',
            $user->getUsername());

        //change password
        $form = $this->userForm($crawler, 'Modifier','editedUser', 'pass2', 'pass2', 'user1@localhost.com');
        $crawler = $client->submit($form);
        $this->assertTrue($client->getResponse()->isRedirect('/users'));
        $client->followRedirect();

        //logout and log back in with new password
        $crawler = $client->request('GET', '/logout');
        $client = $this->loginClient($client, 'editedUser', 'pass2');

        //return to editing to check all is ok
        $crawler = $client->request('GET', '/users/' . $user->getId() . '/edit');
        $this->assertEquals(200, $client->getResponse()->getStatusCode());

        //checking we have the username in bold for editing
        $this->assertSelectorTextContains('html div>h1',
            'Modifier');
        $this->assertSelectorTextContains('html div>h1>strong',
            'editedUser');

    }


    private function userForm(Crawler $crawler, $buttonText, $username, $pass1, $pass2, $mail)
    {
        $form = $crawler->selectButton($buttonText)->form();
        $form['user[username]'] = $username;
        $form['user[password][first]'] = $pass1;
        $form['user[password][second]'] = $pass2;
        $form['user[email]'] = $mail;
        return $form;
    }
}
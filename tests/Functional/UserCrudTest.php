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
        $this->assertEquals(200, $client->getResponse()->getStatusCode(), 'Login page did not respond with a 200 code');

        //filling out the login form
        $form = $crawler->selectButton('Se connecter')->form();
        $form['username'] = 'aNonExistentUser';
        $form['password'] = 'badPassword';
        $crawler = $client->submit($form);

        //we should be sent back to the login form
        $this->assertTrue($client->getResponse()->isRedirect('/login'), 'We were not redirected to the login page after a bad user form submission');
        $crawler = $client->followRedirect();

        //check that we have a flash
        $this->assertSelectorTextContains('html div.alert-danger', 'Le nom d\'utilisateur est incorrect.', 'the error flash message is incorrect');

        //connect with bad password
        $form = $crawler->selectButton('Se connecter')->form();
        $form['username'] = HelperConstants::TEST_USER;
        $form['password'] = 'badPassword';
        $crawler = $client->submit($form);

        $this->assertTrue($client->getResponse()->isRedirect('/login'), 'Did not redirect to login after bad password');
        $crawler = $client->followRedirect();

        $this->assertSelectorTextContains('html div.alert-danger', 'Identifiants invalides.', 'We did not get a flash message for bas password');
    }

    //create user with errors
    public function testCreateUserError()
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/users/create');
        $this->assertEquals(200, $client->getResponse()->getStatusCode(), 'The user/create page did not respond with a 200 code');

        //check that same password
        $form = $this->userForm($crawler, 'Ajouter', 'badUser', 'pass', 'pass2', 'mail@localhost.com');
        $crawler = $client->submit($form);
        $this->assertEquals(200, $client->getResponse()->getStatusCode(), 'We did not get a 200 status code after a bad user creation');
        $this->assertSelectorTextContains('html span.form-error-message',
            'Les deux mots de passe doivent correspondre.', 'We did not get a flash message saying thet the passwords did not match');

    }

    //create a good user
    public function testCreateUser()
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/users/create');
        $this->assertEquals(200, $client->getResponse()->getStatusCode(), 'The user/create page did not respond with a 200 code');

        $form = $this->userForm($crawler, 'Ajouter', 'goodUser', 'pass', 'pass', 'mail@localhost.com');
        $crawler = $client->submit($form);

        $this->assertTrue($client->getResponse()->isRedirect('/users'), 'We were not redirected to /users after an account creation');
        $crawler = $client->followRedirect();


        $user = $this->getUser($this->entityManager, 'goodUser');
        $this->assertNotNull($user, 'The user was not created in the DataBase');
        $this->assertEquals('mail@localhost.com', $user->getEmail(), 'The new user email was not registered in the database');
    }

    //login as admin and edit a username
    public function testEditUser()
    {
        $client = static::createClient();
        $client = $this->loginAdmin($client);

        /** @var User $user */
        $user = $this->getUser($this->entityManager);
        $crawler = $client->request('GET', '/users/' . $user->getId() . '/edit');
        $this->assertEquals(200, $client->getResponse()->getStatusCode(), '/users/' . $user->getId() . '/edit did not respond with a 200 status code when logged in as admin');

        //checking we have the username in bold for editing
        $this->assertSelectorTextContains('html div>h1',
            'Modifier', 'We did not find \'Modifier\' on the edit page while logged in as admin');
        $this->assertSelectorTextContains('html div>h1>strong',
            $user->getUsername(), $user->getUsername() . ' not in bold on the edit page while logged in as admin');

        $form = $this->userForm($crawler, 'Modifier', 'editedUser', 'pass', 'pass', 'user1@localhost.com');
        $crawler = $client->submit($form);
        $this->assertTrue($client->getResponse()->isRedirect('/users'), 'Not redirected to /users after editing a user as admin');
        $user = $this->getUser($this->entityManager, 'editedUser');
        $this->assertNotNull($user, 'Could not find the edited user in the database');
    }

    //log in as a user and update our password
    public function testEditUserPassword()
    {
        $client = static::createClient();
        $client = $this->loginClient($client);

        /** @var User $user */
        $user = $this->getUser($this->entityManager);
        $crawler = $client->request('GET', '/users/' . $user->getId() . '/edit');
        $this->assertEquals(200, $client->getResponse()->getStatusCode(), '/users/' . $user->getId() . '/edit did not respond with a 200 code when logged in as user');

        //checking we have the username in bold for editing
        $this->assertSelectorTextContains('html div>h1',
            'Modifier', 'We did not find \'Modifier\' on the edit page while logged in as user');
        $this->assertSelectorTextContains('html div>h1>strong',
            $user->getUsername(), $user->getUsername() . ' not in bold on the edit page while logged in as user');

        //change password
        $form = $this->userForm($crawler, 'Modifier', 'editedUser', 'pass2', 'pass2', 'user1@localhost.com');
        $crawler = $client->submit($form);
        $this->assertTrue($client->getResponse()->isRedirect('/users'), 'did not redirect to /users after password update');
        $client->followRedirect();

        //logout and log back in with new password
        $crawler = $client->request('GET', '/logout');
        $client = $this->loginClient($client, 'editedUser', 'pass2');

        //return to editing to check all is ok
        $crawler = $client->request('GET', '/users/' . $user->getId() . '/edit');
        $this->assertEquals(200, $client->getResponse()->getStatusCode(), 'We could not edit our user after logout and login with new password');

        //checking we have the username in bold for editing
        $this->assertSelectorTextContains('html div>h1',
            'Modifier', 'We did not find \'Modifier\' on the edit page while logged in as user after password update');
        $this->assertSelectorTextContains('html div>h1>strong',
            'editedUser', $user->getUsername() . ' not in bold on the edit page while logged in as user after password update');

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
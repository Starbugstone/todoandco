<?php


namespace App\Tests\Functional;


use Doctrine\ORM\EntityManager;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

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

        $this->assertTrue($client->getResponse()->isRedirect('/login'));
        $crawler = $client->followRedirect();

        $this->assertSelectorTextContains('html div.alert-danger', 'Le nom d\'utilisateur est incorrect.');

        $form = $crawler->selectButton('Se connecter')->form();
        $form['username'] = HelperConstants::TEST_USER;
        $form['password'] = 'badPassword';
        $crawler = $client->submit($form);

        $this->assertTrue($client->getResponse()->isRedirect('/login'));
        $crawler = $client->followRedirect();

        $this->assertSelectorTextContains('html div.alert-danger', 'Identifiants invalides.');

    }
}
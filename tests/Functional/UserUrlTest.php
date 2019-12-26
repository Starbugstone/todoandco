<?php

namespace App\Tests\Functional;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class UserUrlTest extends WebTestCase
{
    use HelperTrait;

    /**
     * Test url's that need auth when not logged in
     * @dataProvider provideAuthUrls
     */
    public function testNonLoggedInAuthUrl($url)
    {
        $client = static::createClient();
        $crawler = $client->request('GET', $url);

        //we are not logged in, get a redirect to login
        $this->assertTrue($client->getResponse()->isRedirect('/login'));
        $crawler = $client->followRedirect();
    }

    /**
     * tes URL's that don't need auth
     * @dataProvider provideNonAuthUrls
     */
    public function testNonLoggedInNonAuthUrl($url)
    {
        $client = static::createClient();
        $crawler = $client->request('GET', $url);

        $this->assertEquals(200, $client->getResponse()->getStatusCode());
    }


    /**
     * test logged in url's when we are logged in
     * @dataProvider provideAuthUrls
     */
    public function testLoggedInAuthUrls($url)
    {
        $client = static::createClient();
        $client = $this->loginClient($client);

        $crawler = $client->request('GET', $url);
        $this->assertEquals(200, $client->getResponse()->getStatusCode());
    }

    //test that we redirect to homepage after login
    public function testLoginRedirect()
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/login');
        $this->assertEquals(200, $client->getResponse()->getStatusCode());

        //filling out the login form
        $form = $crawler->selectButton('Se connecter')->form();
        $form['username'] = HelperConstants::TEST_USER;
        $form['password'] = HelperConstants::TEST_PASSWORD;
        $crawler = $client->submit($form);

        $this->assertTrue($client->getResponse()->isRedirect('/'));
        $crawler = $client->followRedirect();
    }

    //test that we redirect to requested page after login
    public function testLoginRedirectSpecificPage()
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/tasks');
        $this->assertTrue($client->getResponse()->isRedirect('/login'));
        $crawler = $client->followRedirect();

        //filling out the login form
        $form = $crawler->selectButton('Se connecter')->form();
        $form['username'] = HelperConstants::TEST_USER;
        $form['password'] = HelperConstants::TEST_PASSWORD;
        $crawler = $client->submit($form);

        $this->assertTrue($client->getResponse()->isRedirect()); //can't test the redirect directly as we are passed the absolute URL
        $this->assertStringContainsString('/tasks', $client->getResponse()->headers->get('location'));

    }

    //test we redirect to home page if logged in
    public function testLoggedInRedirect()
    {
        $client = static::createClient();
        $client = $this->loginClient($client);
        $crawler = $client->request('GET', '/login');

        $this->assertTrue($client->getResponse()->isRedirect('/'));
        $crawler = $client->followRedirect();
    }

    public function provideAuthUrls()
    {
        return [
            ['/'],
            ['/tasks'],
            ['/tasks/create'],
        ];
    }

    public function provideNonAuthUrls()
    {
        return [
            ['/users'],
            ['/users/create'],
        ];
    }

}
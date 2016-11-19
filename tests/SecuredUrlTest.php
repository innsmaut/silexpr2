<?php

namespace MyTests;

use Silex\WebTestCase;

class SecuredUrlsTest extends WebTestCase
{
    public function createApplication()
    {
        $app = null;
        require __DIR__ . '/../web/index.php';
        return $app;
    }

    public function testSecuredPageIsAvailable(){
        $client = $this->createClient();
        $client->request('GET', '/c0fe6798d299d050259a21c821583c58');

        $this->assertTrue($client->getResponse()->isOk());
    }

    public function testSecuredPageRedirectToMain(){
        $client = $this->createClient();
        $crawler = $client->request('GET', '/c0fe6798d299d050259a21c821583c58');

        $link = $crawler->filter('a:contains("Back to main page.")')->link();
        $this->assertEquals('http://localhost/', $link->getUri());

        $client->click($link);
        $this->assertTrue($client->getResponse()->isOk());
    }

    public function testSecuredOutdatedPageIsNotAvailable(){
        $client = $this->createClient();
        $client->request('GET', '/e46e82ce92b095d20a1862cd13c8da52');

        $this->assertTrue($client->getResponse()->isNotFound());
    }

    public function testPasswordInputIncorrect(){
        $client = $this->createClient();
        $crawler = $client->request('GET', '/c0fe6798d299d050259a21c821583c58');

        $form = $crawler->selectButton('Submit')->form();
        $form['form[password]'] = 'wrong';
        $client->submit($form);

        $this->assertFalse($client->getResponse()->isRedirect());
    }

    public function testPasswordInputCorrect(){
        $client = $this->createClient();
        $crawler = $client->request('GET', '/c0fe6798d299d050259a21c821583c58');

        $form = $crawler->selectButton('Submit')->form();
        $form['form[password]'] = '123';
        $client->submit($form);

        $this->assertTrue($client->getResponse()->isRedirect());
    }
}

<?php

namespace MyTests;

use Silex\WebTestCase;

class UnsecuredUrlsTest extends WebTestCase
{
    public function createApplication()
    {
        $app = null;
        require __DIR__ . '/../web/index.php';
        return $app;
    }

    public function testUnsecuredPageIsAvailable(){
        $client = $this->createClient();
        $client->request('GET', '/b67dc54833a303acdeae60893892919b');

        $this->assertTrue($client->getResponse()->isRedirect());
    }

    public function testUnsecuredOutdatedPageIsNotAvailable(){
        $client = $this->createClient();
        $client->request('GET', '/5ef370c68df50c74a76c9e2bd2494a17');

        $this->assertTrue($client->getResponse()->isNotFound());
    }
    
    public function testNotExistingPageIsNotAvailable(){
        $client = $this->createClient();
        $client->request('GET', '/not_existing_page');

        $this->assertTrue($client->getResponse()->isNotFound());
    }
}

<?php

namespace MyTests;

use Silex\WebTestCase;

class MainPageTest extends WebTestCase
{
    public function createApplication()
    {
        $app = null;
        require __DIR__ . '/../web/index.php';
        return $app;
    }

    public function testPage(){
        $client = $this->createClient();
        $client->request('GET', '/');

        $this->assertTrue($client->getResponse()->isOk());
    }

    public function testTime(){
        $client = $this->createClient();
        $crawler = $client->request('GET', '/');

        $this->assertCount(1, $crawler->filter('p:contains("Current time is:")'));
        $this->assertContains(date_create()->format('Y-m-d H:i:s'),
            $crawler->filter('p:contains("Current time is:")')->getNode(0)->textContent);
    }

    public function testCreateLink(){
        $client = $this->createClient();
        $crawler = $client->request('GET', '/');

        $link = $crawler->filter('a:contains("Create new entry")')->link();
        $this->assertContains('create', $link->getUri());
        $client->click($link);
        $this->assertTrue($client->getResponse()->isOk());
    }
}

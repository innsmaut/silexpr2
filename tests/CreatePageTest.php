<?php

namespace MyTests;

use Silex\WebTestCase;

class CreatePageTest extends WebTestCase
{
    public function createApplication()
    {
        $app = null;
        require __DIR__ . '/../web/index.php';
        return $app;
    }
    
    public function testCreatePageAvailable(){
        $client = $this->createClient();
        $client->request('GET', '/create');

        $this->assertTrue($client->getResponse()->isOk());
        }

    /**
     * @dataProvider creatingFormProvider
     */
    public function testCreatingPages($link, $expired_on, $password){
        $client = $this->createClient();
        $crawler = $client->request('GET', '/create');

        $form = $crawler->selectButton('Submit')->form();

        $form['form[claimed_link]'] = $link;
        $form['form[expired_on][minute]'] = $expired_on;
        $form['form[password]'] = $password;

        $client->submit($form);
        
        if($link !== ''){
            $this->assertTrue($client->getResponse()->isOk());
        } else {
            $this->assertFalse($client->getResponse()->isOk());
        }

        if ($client->getResponse()->isOk()){
            $this->app['dbn']->deleteLink(['claimed_link' => $link]);
        }
    }

    public function creatingFormProvider(){
        $link = 'http://silex.sensiolabs.org/doc/master/testing.html';
        return [
            'blank_form' => ['', 0, ''],
            'bare_link' => [$link, 0, ''],
            'link_and_expiration_time' => [$link, 1, ''],
            'link_time_password' => [$link, 1, 'secret']
        ];
    }

    public function testSecuredPageRedirectToMain(){
        $client = $this->createClient();
        $crawler = $client->request('GET', '/create');

        $link = $crawler->filter('a:contains("Back to main page.")')->link();
        $this->assertEquals('http://localhost/', $link->getUri());

        $client->click($link);
        $this->assertTrue($client->getResponse()->isOk());
    }
}

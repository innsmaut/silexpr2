<?php

namespace MyControllers;

use Silex\Application;
use Silex\ControllerProviderInterface;

class DeletePageController implements ControllerProviderInterface
{
    public function connect(Application $app)
    {
        $controllers = $app['controllers_factory'];
        $controllers->get('/delete{id}', function ($id) use($app){
            $app['dbn']->deleteLink(['id' => $id]);
            
            return $app->redirect($_SERVER['HTTP_REFERER'], 302);
        })->bind('delete');
        return $controllers;
    }
}
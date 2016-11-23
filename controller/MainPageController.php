<?php

namespace MyControllers;

use Silex\Application;
use Silex\ControllerProviderInterface;

class MainPageController implements ControllerProviderInterface
{
    public function connect(Application $app)
    {
        $controllers = $app['controllers_factory'];
        $controllers->get('/', function () use ($app) {
            $result = $app['dbn']->getSelect();
            return $app['twig']->render('main.twig', ['result' => $result]);
        })->bind('main');
        return $controllers;
    }
}
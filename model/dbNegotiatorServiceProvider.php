<?php

namespace MyModels;

use Silex\Application;
use Silex\ServiceProviderInterface;

class dbNegotiatorServiceProvider implements ServiceProviderInterface
{
    public function register(Application $app)
    {
        $app['dbn.config'] = [];
        
        $app['dbn'] = $app->share(function ($app) {
            return  dbNegotiator::getInstance($app['dbn.config']);
        });
    }

    public function boot(Application $app)
    {
    }
}

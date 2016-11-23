<?php

namespace MyModels;

use Silex\Application;
use Silex\ServiceProviderInterface;

class dbNegotiatorServiceProvider implements ServiceProviderInterface
{
    public function register(Application $app)
    {
        $app['dbn.tableName'] = '';
        $app['dbn.dbConfig'] = [];
        
        $app['dbn'] = $app->share(function ($app) {
            return  new dbNegotiator($app['dbn.tableName'], $app['dbn.dbConfig']);
        });
    }

    public function boot(Application $app)
    {
    }
}

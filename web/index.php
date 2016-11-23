<?php

require_once __DIR__.'/../vendor/autoload.php';

use Silex\Provider\TwigServiceProvider;
use Silex\Provider\UrlGeneratorServiceProvider;
use Silex\Provider\FormServiceProvider;
use Silex\Provider\TranslationServiceProvider;
use Silex\Provider\SessionServiceProvider;
use MyModels\dbNegotiatorServiceProvider;
use MyControllers\MainPageController;
use MyControllers\CreatePageController;
use MyControllers\DeletePageController;
use MyControllers\LinkPageController;

$app = new Silex\Application();

$app->register(new UrlGeneratorServiceProvider());
$app->register(new FormServiceProvider());
$app->register(new TranslationServiceProvider());
$app->register(new SessionServiceProvider(), ['session.test' => false !== getenv('TEST')]); //for testing twig forms
$app->register(new TwigServiceProvider(), ['twig.path' => __DIR__.'/view']);
$app->register(new dbNegotiatorServiceProvider(), ['dbn.config' => require 'dbconf.php']);

$app->mount('/', new MainPageController());
$app->mount('/', new CreatePageController());
$app->mount('/', new DeletePageController());
$app->mount('/', new LinkPageController());

$app['debug'] = true;

$app->run();

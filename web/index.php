<?php

require_once __DIR__.'/../vendor/autoload.php';

require_once 'dbNegotiator.php';

use Silex\Provider\TwigServiceProvider;
use Silex\Provider\UrlGeneratorServiceProvider;
use Silex\Provider\FormServiceProvider;
use Silex\Provider\TranslationServiceProvider;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Form\Extension\Core\Type\UrlType;
use Symfony\Component\Form\Extension\Core\Type\TimeType;
use Symfony\Component\Form\Extension\Core\Type\TextType;

$app = new Silex\Application();

$app->register(new TwigServiceProvider(), ['twig.path' => __DIR__.'/view']);
$app->register(new UrlGeneratorServiceProvider());
$app->register(new FormServiceProvider());
$app->register(new TranslationServiceProvider());

//links here is a table name
$app['dbn'] = new dbNegotiator('links', require 'dbconf.php');

$app->get('/srv', function () use ($app) {
    //$result = $app['dbn']->getMain();
    return $app['twig']->render('create.twig', ['result' => []]);
});

$app->match('/create', function (Request $request) use ($app){
    $result = [];
    $form = $app['form.factory']->createBuilder(FormType::class)
        ->add('claimed_link', UrlType::class)
        ->add('expired_on', TimeType::class, ['input' => 'timestamp'])
        ->add('password', TextType::class, ['required' => false])
        ->getForm();
    if(isset($request)){
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()){
            $result = $form->getData();
            $result['redirect_link'] = md5($result['claimed_link'].date_timestamp_get(date_create()));
            $result['expired_on'] += 3600; //this form returns -3600 timestamp instead of 0, maybe locale problems
            if($result['expired_on'] !== 0) {
                $result['expired_on'] += date_timestamp_get(date_create());
            }
            $result['password'] = ($result['password'])?:'';
            $app['dbn']->setNew($result);
        }
    }
    return $app['twig']->render('create.twig', ['result' => $result, 'form'=> $form->createView()]);
});

$app->get('/', function () use ($app) {
    $result = $app['dbn']->getMain();
    return $app['twig']->render('main.twig', ['result' => $result]);
});

$app->match('/create', function (Request $request) use ($app){
    $result = [];
    $form = $app['form.factory']->createBuilder(FormType::class)
        ->add('claimed_link', UrlType::class)
        ->add('expired_on', TimeType::class, ['input' => 'timestamp'])
        ->add('password', TextType::class, ['required' => false])
        ->getForm();
    if(isset($request)){
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()){
            $result = $form->getData();
            $result['redirect_link'] = md5($result['claimed_link'].date_timestamp_get(date_create()));
            $result['expired_on'] += 3600; //this form returns -3600 timestamp instead of 0, maybe locale problems
            if($result['expired_on'] !== 0) {
                $result['expired_on'] += date_timestamp_get(date_create());
            }
            $result['password'] = ($result['password'])?:'';
            $app['dbn']->setNew($result);
        }
    }
    return $app['twig']->render('create.twig', ['result' => $result, 'form'=> $form->createView()]);
});

$app->get('/{link}', function ($link) use ($app){
    $result = $app['dbn']->getLinkGet($link);
    if ($result !== []){
        if ($result[0]['password'] !== '') {
            return $app['twig']->render('password.twig', ['result' => $link]);
        } else {
            return $app->redirect($result[0]['claimed_link']);
        }
    } else {
        return $app->abort(404, 'No link found!');
    }
})->bind('link');

$app->post('/{link}', function ($link) use ($app){
    if (isset($_POST['password_acc'])){
        $result = $app['dbn']->getLinkPost([$link, $_POST['password_acc']]);
        if ($result !== []){
            return $app->redirect($result[0]['claimed_link']);
        } else {
            return $app->abort(404, 'No link found or password is bad!');
        }
    } else {
        return $app->abort(404, "Wrong link!");
    }
})->after(function (){ unset($_POST['password_acc']);});

$app['debug'] = true;

$app->run();

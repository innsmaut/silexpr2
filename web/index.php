<?php

require_once __DIR__.'/../vendor/autoload.php';

use Silex\Provider\TwigServiceProvider;
use Silex\Provider\UrlGeneratorServiceProvider;
use Silex\Provider\FormServiceProvider;
use Silex\Provider\TranslationServiceProvider;
use Silex\Provider\SessionServiceProvider;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Form\Extension\Core\Type\UrlType;
use Symfony\Component\Form\Extension\Core\Type\TimeType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use MyModels\dbNegotiatorServiceProvider;

$app = new Silex\Application();

$app->register(new UrlGeneratorServiceProvider());
$app->register(new FormServiceProvider());
$app->register(new TranslationServiceProvider());
$app->register(new SessionServiceProvider(), ['session.test' => false !== getenv('TEST')]); //for testing twig forms
$app->register(new TwigServiceProvider(), ['twig.path' => __DIR__.'/view']);
$app->register(new dbNegotiatorServiceProvider(), [
    'dbn.tableName' => 'links', //links stands for table name
    'dbn.dbConfig' => require 'dbconf.php'
]);

//main page
$app->get('/', function () use ($app) {
    $result = $app['dbn']->getSelect();
    return $app['twig']->render('main.twig', ['result' => $result]);
})->bind('main');

//handles creating new links
$app->match('/create', function (Request $request) use ($app){
    $result = [];
    $form = $app['form.factory']->createBuilder(FormType::class)
        ->add('claimed_link', UrlType::class)
        ->add('expired_on', TimeType::class, ['input' => 'timestamp'])
        ->add('password', TextType::class, ['required' => false])
        ->getForm();
    //whether @request from 'create'-form exists
    if(isset($request)){
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()){
            $result = $form->getData();
            $result['redirect_link'] = md5($result['claimed_link'].date_create()->getTimestamp());
            $result['expired_on'] += 3600; //winter time shift, local timezone problems
            $result['expired_on'] === 0 || $result['expired_on'] += date_create()->getTimestamp();
            $result['password'] = ($result['password'])?:'';
            $app['dbn']->setNew($result);
        }
    }
    return $app['twig']->render('create.twig', ['result' => $result, 'form'=> $form->createView()]);
})->bind('create');

//delete link from database
$app->get('/delete{id}', function ($id) use($app){
    $app['dbn']->deleteLink(['id' => $id]);
    return $app->redirect('/');
})->bind('delete');

//handles access to created links
$app->match('/{link}', function ($link, Request $request) use ($app){
    $result = $app['dbn']->getSelect(['redirect_link' => $link]);
    //whether link was found
    if ($result !== []){
        //whether password is required
        if ($result[0]['password'] !== '') {
            $form = $app['form.factory']->createBuilder(FormType::class)
                ->add('password', TextType::class)->getForm();
            //whether POST @request from 'password'-form exists
            if(isset($request) && $request->getMethod() === 'POST'){
                $form->handleRequest($request);
                if ($form->isSubmitted() && $form->isValid()){
                    $result = $app['dbn']->getSelect([
                        'redirect_link' => $link,
                        'password' => $form->getData()['password']
                    ]);
                    if ($result !== []){
                        return $app->redirect($result[0]['claimed_link']);
                    } else {
                        return $app->abort(404, 'No link found or password is bad!');
                    }
                }
            } else {
                return $app['twig']->render('password.twig', ['result' => $link, 'form'=> $form->createView()]);
            }
        } else {
            return $app->redirect($result[0]['claimed_link']);
        }
    }
    return $app->abort(404, 'No link found!');
})->bind('link');

$app['debug'] = true;

$app->run();

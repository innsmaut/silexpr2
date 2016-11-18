<?php

require_once __DIR__.'/../vendor/autoload.php';

require_once 'dbNegotiator.php';

$app = new Silex\Application();

$app->register(new Silex\Provider\TwigServiceProvider(), array(
    'twig.path' => __DIR__.'/view',
));
$app->register(new Silex\Provider\UrlGeneratorServiceProvider());

//links here is a table name
$app['dbn'] = new dbNegotiator('links', require 'dbconf.php');

$app->get('/srv', function () use ($app) {
    //$result = $app['dbn']->getMain();
    return $app['twig']->render('create.twig', ['result' => []]);
});

$app->get('/', function () use ($app) {
    $result = $app['dbn']->getMain();
    return $app['twig']->render('main.twig', ['result' => $result]);
});

$app->get('/create', function () use ($app) {
    return $app['twig']->render('create.twig', ['result' => []]);
})->bind('create');

//unsetting POST variables to prevent reuse
$app->post('/create', function () use($app) {
    if (isset($_POST['claimed_link'])){
        $redirectLink = md5($_POST['claimed_link'].date_timestamp_get(date_create()));
        $expiredOn = ($_POST['expired_on'] === '')?'':date_timestamp_get(date_add(date_create(),
            date_interval_create_from_date_string($_POST['expired_on'].' minutes')));
        $newLine = [
            'claimed_link' => $_POST['claimed_link'],
            'redirect_link' => $redirectLink,
            'password' => $_POST['password'],
            'expired_on' => $expiredOn
        ];
        $app['dbn']->setNew($newLine);
        return $app['twig']->render('create.twig', ['result' => $newLine]);
    }
})->after(function (){unset($_POST['claimed_link'], $_POST['expired_on'], $_POST['password']);});

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

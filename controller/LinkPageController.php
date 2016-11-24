<?php

namespace MyControllers;

use Silex\Application;
use Silex\ControllerProviderInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\Extension\Core\Type\TextType;

class LinkPageController implements ControllerProviderInterface
{
    public function connect(Application $app)
    {
        $controllers = $app['controllers_factory'];

        //handle unsecured links
        $controllers->get('/{link}', function ($link) use ($app){
            $result = $app['dbn']->getSelect(['redirect_link' => $link]);

            if ($result !== []){
                return ($result[0]['password'] !== '')?
                    $app->redirect($link.'/password', 302):
                    $app->redirect($result[0]['claimed_link']);
            }
            return $app->abort(404, 'No link found!');
        })->bind('link');

        //handle links with password
        $controllers->match('/{link}/password', function ($link, Request $request) use($app){
            $form = $app['form.factory']->createBuilder(FormType::class)
                ->add('password', TextType::class)->getForm();

            if(isset($request) && $request->getMethod() === 'POST'){
                $form->handleRequest($request);
                if ($form->isSubmitted() && $form->isValid()){
                    $result = $app['dbn']->getSelect([
                        'redirect_link' => $link,
                        'password' => $form->getData()['password']
                    ]);
                    return ($result !== [])?
                        $app->redirect($result[0]['claimed_link']):
                        $app->abort(404, 'Incorrect password!');
                }
            }
            return $app['twig']->render('password.twig', ['result' => $link, 'form'=> $form->createView()]);
        });

        return $controllers;
    }
}
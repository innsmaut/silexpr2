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
        $controllers->match('/{link}', function ($link, Request $request) use ($app){
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

        return $controllers;
    }
}
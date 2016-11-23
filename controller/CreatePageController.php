<?php

namespace MyControllers;

use Silex\Application;
use Silex\ControllerProviderInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\Extension\Core\Type\UrlType;
use Symfony\Component\Form\Extension\Core\Type\TimeType;
use Symfony\Component\Form\Extension\Core\Type\TextType;

class CreatePageController implements ControllerProviderInterface
{
    public function connect(Application $app)
    {
        $controllers = $app['controllers_factory'];
        $controllers->match('/create', function (Request $request) use ($app){
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
        return $controllers;
    }
}
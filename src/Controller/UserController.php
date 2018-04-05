<?php
namespace App\Controller;

use Twig\Environment;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use App\Entity\User;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class UserController
{
    public function registerUser
    (
        Environment $twig, 
        FormFactoryInterface $factory,
        Request $request,
        ObjectManager $manager,
        SessionInterface $session,
        UrlGeneratorInterface $urlGenerator
    )
    {
        $user = new User();
        $builder = $factory->createBuilder(FormType::class, $user);
        
        $builder
        ->add(
            'username', 
            TextType::class,
            [
                'required' => true
            ]
            )
        ->add(
            'firstname', 
            TextType::class,
            [
                'required' => true
            ]
            )
        ->add(
            'lastname', 
            TextType::class,
            [
                'required' => true
            ]
            )
        ->add(
            'email', 
            EmailType::class,
            [
                'required' => true
            ]
            )
        ->add(
            'password', 
            RepeatedType::class,
            [
                'type' => PasswordType::class,
                'invalid_message' => 'The password fields must match.',
                'options' => array('attr' => array('class' => 'password-field')),
                'required' => true,
                'first_options'  => array('label' => 'Password'),
                'second_options' => array('label' => 'Repeat Password'),
            ]
            )
        ->add(
            'submit',
            SubmitType::class,
            [
                'attr' => [
                    'class' => 'btn-block btn-success'
                ]
            ]
            );
        
        $form = $builder->getForm();
        $form->handleRequest($request);
        
        if($form->isSubmitted() && $form->isValid()) {
            $manager->persist($user);
            $manager->flush();
            
            $session->getFlashBag()->add('info', 'User successfully registered !');
            
            return new RedirectResponse($urlGenerator->generate('homepage'));
        }
      
        return new Response($twig->render('User/registerUser.html.twig', [
            'formRegister' => $form->createView()
        ]));
    }
}


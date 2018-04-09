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
use App\Repository\UserRepository;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Security\Core\Encoder\EncoderFactoryInterface;
use App\Repository\RoleRepository;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class UserController
{
    public function registerUser
    (
        Environment $twig, 
        FormFactoryInterface $factory,
        Request $request,
        ObjectManager $manager,
        SessionInterface $session,
        UrlGeneratorInterface $urlGenerator,
        \Swift_Mailer $mailer,
        EncoderFactoryInterface $encoderFactory,
        RoleRepository $roleRepository
    )
    {
        $user = new User();
        $builder = $factory->createBuilder(FormType::class, $user);
        
        $builder
        ->add(
            'username', 
            TextType::class,
            [
                'label' => 'FORM.USER.USERNAME',
                'required' => true
            ]
            )
        ->add(
            'firstname', 
            TextType::class,
            [
                'label' => 'FORM.USER.FIRSTNAME',
                'required' => true
            ]
            )
        ->add(
            'lastname', 
            TextType::class,
            [
                'label' => 'FORM.USER.LASTNAME',
                'required' => true
            ]
            )
        ->add(
            'email', 
            EmailType::class,
            [
                'label' => 'FORM.USER.EMAIL',
                'required' => true
            ]
            )
        ->add(
            'password', 
            RepeatedType::class,
            [
                'label' => '',
                'type' => PasswordType::class,
                'invalid_message' => 'The password fields must match.',
                'options' => array('attr' => array('class' => 'password-field')),
                'required' => true,
                'first_options'  => array('label' => 'FORM.USER.PASSWORD'),
                'second_options' => array('label' => 'FORM.USER.REPEATPASSWORD'),
            ]
            )
        ->add(
            'submit',
            SubmitType::class,
            [
                'label' => 'FORM.USER.SUBMIT',
                'attr' => [
                    'class' => 'btn-block btn-success'
                ]
            ]
            );
        
        $form = $builder->getForm();
        $form->handleRequest($request);
        
        if($form->isSubmitted() && $form->isValid()) {
            //md5 creates a digest message, a strong to validate the matching of 2 elements
            $salt = md5($user->getUsername());
            $user->setSalt($salt);
            
            $encoder = $encoderFactory->getEncoder(User::class);
            $password = $encoder->encodePassword(
                $user->getPassword(), 
                $salt
            );
            
            $user->setPassword($password);
            
            $user->addRole($roleRepository->findOneByLabel('ROLE_USER'));
            
            $manager->persist($user);
            $manager->flush();
            
            //To send an email with Swift_Mailer
            $message = new \Swift_Message();
            $message->setFrom('ivanmendoza@gmail.com')
            ->setTo($user->getEmail())
            ->setSubject('Validate your account.')
            ->setContentType('text/html')
            ->setBody(
                $twig->render(
                    'Mail/accountCreation.html.twig',
                    ['user' => $user]
                )
                
            )->addPart(
                $twig->render(
                    'Mail/accountCreation.txt.twig',
                    ['user' => $user]
                )
                ,'text/plain'
            );
            
            $mailer->send($message);
            
            $session->getFlashBag()->add('info', 'User successfully registered !');
            
            return new RedirectResponse($urlGenerator->generate('homepage'));
        }
      
        return new Response($twig->render('User/registerUser.html.twig', [
            'formRegister' => $form->createView()
        ]));
    }
    
    public function activateUser
    (
        $token, 
        ObjectManager $manager,
        SessionInterface $session,
        UrlGeneratorInterface $urlGenerator,
        RoleRepository $roleRepository
    ) 
    {
        $userRepository = $manager->getRepository(User::class);
        
        $user = $userRepository->findOneByEmailToken($token);
        
        if(!$user) {
            throw new NotFoundHttpException('User not found for the given token');
        }
        
        $user
        ->setActive(true)
        ->setEmailToken(null)
        ->addRole($roleRepository->findOneByLabel('ROLE_ACTIVE'));
        
        $manager->flush();

        $session->getFlashBag()->add('info', 'User successfully activated !');

        return new RedirectResponse($urlGenerator->generate('homepage'));
    }
    
    public function usernameAvailable(Request $request, UserRepository $userRepository) 
    {
        $username = $request->request->get('username');
        
        $usernameUnavailable = false;
        if(!empty($username)) {
            $usernameUnavailable = $userRepository->usernameAvailable($username);
        }
        
        return new JsonResponse([
            'available' => !$usernameUnavailable
        ]);
    }
    
    public function login
    (
        AuthenticationUtils $authUtils,
        Environment $twig
    )
    {
        return new Response(
            $twig->render(
                'Security/login.html.twig',
                [
                    'last_username' => $authUtils->getLastUsername(),
                    'error' => $authUtils->getLastAuthenticationError()
                ]
            )
        );
        
    }
}
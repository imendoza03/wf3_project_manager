<?php
namespace App\Controller;

use Twig\Environment;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use App\Entity\Product;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class ProductController
{
    public function addProduct
    (
        Environment $twig, 
        FormFactoryInterface $factory, 
        Request $request,
        ObjectManager $manager,
        SessionInterface $session,
        UrlGeneratorInterface $urlGenerator
    ) 
    {
        $product = new Product();
        $builder = $factory->createBuilder(FormType::class, $product);
        
        $builder
        ->add(
            'name', 
            TextType::class,
            [
                'required' => true,
                'label' => 'FORM.PRODUCT.NAME'
            ]
         )
        ->add(
            'description', 
            TextareaType::class, 
            [
                'label' => 'FORM.PRODUCT.DESCRIPTION',
                'required' => false,
                'attr' => [
                    'class' => 'form-control'
                ]
            ]
         )
        ->add(
            'version', 
            TextType::class,
            [
                'label' => 'FORM.PRODUCT.VERSION'
            ]
        )
        ->add(
            'submit', 
            SubmitType::class,
            [
                'label' => 'FORM.PRODUCT.SUBMIT',
                'attr' => [
                    'class' => 'btn-block btn-success'
                ]
            ]
         );
        
       $form = $builder->getForm();
       
       $form->handleRequest($request);
       if($form->isSubmitted() && $form->isValid()) {
           $manager->persist($product);
           $manager->flush();
          
           //getFlashBag() is not defined in the session
           $session->getFlashBag()->add('info', 'Your product has been created :) !');
           
           return new RedirectResponse($urlGenerator->generate('homepage'));    
       }
        
       return new Response(
           $twig->render('Product/addProduct.html.twig', 
           [
            'formular' => $form->createView()
           ])
       );
    }
    
    public function listProduct
    (
        Environment $twig,
        ObjectManager $manager
    ) 
    {
        $productRepository = $manager->getRepository(Product::class);
        $products = $productRepository->findAll();
        
        return new Response
        (
            $twig->render
            (
                'Product/listProduct.html.twig',
                ['products' => $products]
            )
        );
    }
}


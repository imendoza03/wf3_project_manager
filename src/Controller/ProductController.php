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
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use App\Repository\ProductRepository;
use App\Entity\Comment;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use App\Form\CommentType;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use App\Entity\CommentFile;
use Ramsey\Uuid\Uuid;

class ProductController
{
    public function addProduct
    (
        Environment $twig, 
        FormFactoryInterface $factory, 
        Request $request,
        ObjectManager $manager,
        SessionInterface $session,
        UrlGeneratorInterface $urlGenerator,
        TokenStorageInterface $tokenStorage
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
        ProductRepository $productRepository
    ) 
    {
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
    
    public function productDetail
    (
        int $id,
        ProductRepository $productRepository,
        Environment $twig,
        FormFactoryInterface $formFactory,
        Request $request,
        ObjectManager $manager,
        UrlGeneratorInterface $urlGenerator
    ) 
    {
        $product = $productRepository->findOneById($id);
        if(!$product) {
            throw new NotFoundHttpException();
        }
        
        $comment = new Comment();
        $form = $formFactory->create(
            CommentType::class,
            $comment,
            ['stateless' => true]
        );
        
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $tmpCommentFile = [];
            
            foreach ($comment->getFiles() as $fileArray) {
                foreach ($fileArray as $file) {
                    $name = sprintf(
                        '%s.%s',
                        Uuid::uuid1(),
                        $file->getClientOriginalExtension()
                    );
                    
                    $commentFile = new CommentFile();
                    $commentFile->setComment($comment)
                    ->setMimeType($file->getMimeType())
                    ->setName($file->getClientOriginalName())
                    ->setFileUrl('/upload/'.$name);
                    
                    $tmpCommentFile[] = $commentFile;

                    $file->move(
                        __DIR__.'../../public/upload',
                        $name
                    );
                    
                    $manager->persist($commentFile);
                }
            }
            
            $token = $tokenStorage->getToken();
            if (!$token){
                throw new \Exception();
            }
            
            $user = $token->getUser();
            if (!$user){
                throw new \Exception();
            }
            
            $comment->setFiles($tmpCommentFile)
                ->setAuthor($user)
                ->setProduct($product);
            
            $manager->persist($comment);
            $manager->flush();
            
            return new RedirectResponse
            (
                $urlGenerator->generate
                (
                    'productDetail',
                    [
                        'product' => $product 
                    ]
                )
            );
        }
        
        return new Response(
            $twig->render(
                'Product/productDetail.html.twig',
                [
                    'product' => $product,
                    'form' => $form->createView()
                ]
            )
        );
    }
}


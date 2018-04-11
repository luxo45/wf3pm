<?php
namespace App\Controller;

use Twig\Environment;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Form\FormFactoryInterface;
use App\Entity\CraftProduct;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\Tests\RequestContentProxy;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use App\Repository\CraftProductRepository;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use App\Dto\FileDto;
use App\Form\CommentFileType;
use App\Entity\Comment;
use App\Form\CommentType;


class ProductController
{

    public function addProduct(Environment $twig, FormFactoryInterface $factory, Request $request, ObjectManager $manager, SessionInterface $session)
    {
        $product = new CraftProduct();
        $builder = $factory->createBuilder(FormType::class, $product); // create a builder to create a form
        $builder->add('name', TextType::class,
                      ['required' => FALSE, 
                      'label'=> 'FORM.PRODUCT.NAME',])          
                ->add('description', TextareaType::class,
                      ['required' => FALSE, 
                   'label'=> 'FORM.PRODUCT.DESCRIPTION',])
                ->add('version', TextType::class,
                ['required' => FALSE,
                    'label'=> 'FORM.PRODUCT.VERSION',])    
                    ->add('submit', SubmitType::class,[
                        'label'=>'FORM.PRODUCT.SUBMIT']
                );
        
        $form = $builder->getForm();
        
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $manager->persist($product);
            $manager->flush();
            
            $session->getFlashBag()->add('info', 'Your product was created');
            
            return new RedirectResponse('/');
        }
        // create a view of this form
        // $form->createView
        return new Response($twig->render('product/addProduct.html.twig', [
            'formular' => $form->createView()
        ]));
    }
    public function listProduct (Environment $twig, CraftProductRepository $repository)
    
    {
        return new Response(
            $twig->render(
                'Product\listProduct.html.twig',
                ['products'=> $repository->findAll()]
                )
            );
    }
    
    public function displayProduct(
                    Environment $twig,
                    CraftProductRepository $repository,
                    int $product,
                    FormFactoryInterface $formFactory,
                    Request $request,
                    TokenStorageInterface $tokenStorage
        ) {
            $product = $repository->find($product);
            if (!$product) {
                throw new NotFoundHttpException();
            }
            
            $comment = new Comment();
            $form = $formFactory->create(
            CommentType::class,
            $comment,
            ['stateless' => true]
        );
            
         $form->handelRequest($request);
         if ($form->isSubmitted() && $form->isValid()){
             
             $commentFiles = [];
         
             /*@var UploadedFile $file
              * 
              */
              
             foreach ($comment->getFiles()as $fileArray){
                 foreach ($fileArray as $file){
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
                        __DIR__.'/../../public/upload',
                        $name
                        );
                    
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
         
         return  new RedirectResponse($urlGenerator->generate('product', ['product'=> $product->getId]));
  
         }
            return new Response(
                $twig->render(
                    'Product/product.html.twig',
                    [
                        'product' => $product,
                        'routeAttr' => ['product' => $product->getId()],
                                   'routeAttr' => ['product' => $product->getId()],
                             'form' => $form->createView()
                    ]
                )
           );

    }

}


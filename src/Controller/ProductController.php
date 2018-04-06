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
}

    


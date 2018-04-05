<?php
namespace App\Controller;

use Twig\Environment;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Form\FormTypeInterface;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use App\Entity\User;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Routing\Generator\UrlGenerator;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class UserController
{

    public function addUser(
        Environment $twig,
        FormFactoryInterface $factory,
        Request $request,
        ObjectManager $manager,
        SessionInterface $session,
        UrlGeneratorInterface $urlGenerator,
        \Swift_Mailer $mailer
    ) {
        $user = new User();
        
        $builder = $factory->createBuilder(FormType::class, $user);
        $builder->add('username', TextType::class)
            ->add('firstname', TextType::class)
            ->add('lastname', TextType::class)
            ->add('email', EmailType::class)
            ->add('password', RepeatedType::class, array(
            'type' => PasswordType::class,
            'invalid_message' => 'The password fields must match.',
            'options' => array(
                'attr' => array(
                    'class' => 'password-field'
                )
            ),
            'required' => true,
            'first_options' => array(
                'label' => 'Password'
            ),
            'second_options' => array(
                'label' => 'Repeat Password'
            )
        ))
            ->add('submit', SubmitType::class);
        
        $form = $builder->getForm();
        
        $form->handleRequest($request);
        
        if ($form->isSubmitted() && $form->isValid()) {
            $manager->persist($user);
            $manager->flush();
            
            // si tout est valide on envoie un message à l'utilisateur//
            $message = new \Swift_Message();
            $message->setFrom('wf3pm@localhost.com')
                ->setTo($user->getEmail())
                ->setSubject('Validate your account')
                ->setBody($twig->render('mail/account_creation.html.twig', [
                'user' => $user
            ]));
                
            $mailer->send($message);
            
            $session->getFlashBag()->add('info', 'you are registered');
            
            return new RedirectResponse($urlGenerator->generate('homepage'));
        }
        return new Response($twig->render('user/addUser.html.twig', [
            'formular' => $form->createView()
        ]));
    }

    public function activateUser(
        $token, 
        ObjectManager $manager, 
        UrlGeneratorInterface $urlGenerator, 
        SessionInterface $session
    ) {
        
        $userRepository = $manager->getRepository(User::class);
        $user = $userRepository->findOneByEmailToken($token);
        
        if (!$user){
            throw new NotFoundHttpException('user not found');
            
        }
        $user->setActive(true)
             ->setEmailToken(null);
        
        $manager->flush();
        $session->getFlashBag()->add('info', 'your email is validated');
        
        return new RedirectResponse($urlGenerator->generate('homepage'));
    }
}



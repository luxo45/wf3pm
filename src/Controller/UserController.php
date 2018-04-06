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
use App\Repository\UserRepository;
use Symfony\Component\HttpFoundation\JsonResponse;

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
        $builder->add('username', TextType::class,[
                      'label'=>'FORM.USER.NAME'])
                ->add('firstname', TextType::class,[
                      'label'=>'FORM.USER.FIRSTNAME'])
                ->add('lastname', TextType::class, [
                      'label'=>'FORM.USER.LASTNAME'])
                ->add('email', EmailType::class,[
                      'label'=>'FORM.USER.EMAIL'])
                ->add('password', RepeatedType::class,[
                    'label'=>'FORM.USER.PASSWORD'],
                    array(
                        'type' => PasswordType::class,
                        'invalid_message' => 'The password fields must match.',
                        'options' => array(
                        'attr' => array(
                        'class' => 'password-field'
                )
            ),
            'required' => true,
            'first_options' => array(
                'label' => 'FORM.USER.PASSWORD'
            ),
            'second_options' => array(
                'label' => 'FORM.USER.REPASS'
            )
        ))
        ->add('submit', SubmitType::class,[
            'label'=>'FORM.USER.SUBMIT']);
        
        $form = $builder->getForm();
        
        $form->handleRequest($request);
        
        if ($form->isSubmitted() && $form->isValid()) {
            $manager->persist($user);
            $manager->flush();
            
            // si tout est valide on envoie un message à l'utilisateur//
            $message = new \Swift_Message();
            $message->setFrom('wf3pm@localhost.com')
                ->setTo($user->getEmail())
                ->setSubject('Validate your account')// pour recevoir un message texte
                ->setBody($twig->render('mail/account_creation.html.twig', [
                'user' => $user
            ])
                )->addPart($twig->render(
                    'mail/account_creation.txt.twig', ['user'=> $user]
                    )
                    , 'text/plain'
                    );
                
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

    public function  usernameAvailable(Request $request, UserRepository $repository)
    {
        $username = $request->request->get('username');
        
        $unavailable = false;
        if (!empty($username)){
            $unavailable = $repository->usernameExist($username);
        }
        
        return new JsonResponse(
            [
                'available' => !$unavailable
            ]
            );
    }
}





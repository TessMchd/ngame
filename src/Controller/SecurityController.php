<?php

namespace App\Controller;

use App\Entity\Stats;
use App\Entity\User;
use App\Form\ModifyPasswordType;
use App\Form\UserRegistrationFormType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Guard\GuardAuthenticatorHandler;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class SecurityController extends AbstractController
{
    /**
     * @Route("/login", name="app_login")
     */
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        // if ($this->getUser()) {
        //     return $this->redirectToRoute('target_path');
        // }

        // get the login error if there is one
        $error = $authenticationUtils->getLastAuthenticationError();
        // last username entered by the user
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('security/login.html.twig', ['last_username' => $lastUsername, 'error' => $error]);
    }

    /**
     * @Route("/logout", name="app_logout")
     */
    public function logout()
    {
        throw new \LogicException('This method can be blank - it will be intercepted by the logout key on your firewall.');
    }

    /**
     * @Route("/register", name="app_register")
     */
    public function register(Request $request, UserPasswordEncoderInterface $passwordEncoder): Response{
        $user = new User();
        $form=$this->createForm(UserRegistrationFormType::class,$user);
        $form->handleRequest($request); // hydratation du form
        if($form->isSubmitted() && $form->isValid()){ // test si le formulaire a été soumis et s'il est valide
            $em = $this->getDoctrine()->getManager(); // on récupère la gestion des entités
            $stats= new Stats();
            $stats->setDefaites(0);
            $stats->setVictoires(0);
            $stats->setPieces(0);
            $stats->setRang(1);
            $em->persist($stats);
            $em->flush();
            $encoded = $passwordEncoder->encodePassword($user,$user->getPassword());
            $user->setAvatar("image_avatar.jpg");
            $user->setPassword($encoded);
            $user->setStats($stats);
            $em->persist($user); // on effectue les mise à jours internes
            $em->flush(); // on effectue la mise à jour vers la base de données
            return $this->redirectToRoute('user_profil', ['id' => $user->getId()]);
            }
            return $this->render('security/register.html.twig', ['form' => $form->createView()]);

    }
    /**
     * @Route("/modify", name="app_modify")
     */
    public function modify(Request $request, UserPasswordEncoderInterface $passwordEncoder) :Response{
        $user = $this->getUser();
        $form=$this->createForm(UserRegistrationFormType::class);
        $form->handleRequest($request); // hydratation du form
        $pwd=$this->createForm(ModifyPasswordType::class);
        $pwd->handleRequest($request);

        if(isset($_POST['avatar'])){
            $user->setAvatar($_POST['avatar']);
            $em = $this->getDoctrine()->getManager(); // on récupère la gestion des entités
            $em->persist($user); // on effectue les mise à jours internes
            $em->flush(); // on effectue la mise à jour vers la base de données
        }
        if($pwd->isSubmitted() && $pwd->isValid()){
            $data=$pwd->getData();
            $em = $this->getDoctrine()->getManager(); // on récupère la gestion des entités
            if($passwordEncoder->isPasswordValid($user, $_POST['actual_pwd'])){
                $encoded = $passwordEncoder->encodePassword($user,$data->getPassword());
                $user->setPassword($encoded);
                $em->persist($user); // on effectue les mise à jours internes
                $em->flush(); // on effectue la mise à jour vers la base de données
                $this->addFlash('message', 'Mot de passe mis à jour');
            }else{
                $error="Le mot de passe actuel n'est pas le bon.";
                return $this->render('security/modify.html.twig', ['form' => $form->createView(),'user'=>$user,'pwd'=>$pwd->createView(),'error'=>$error]);
            }
        }elseif ($pwd->isSubmitted() && $pwd->isValid()==false){
            $error="La confirmation du mot de passe à échouer";
            return $this->render('security/modify.html.twig', ['form' => $form->createView(),'user'=>$user,'pwd'=>$pwd->createView(),'error'=>$error]);
        }
        if($form->isSubmitted() && $form->isValid()){ // test si le formulaire a été soumis et s'il est valide
            $data=$form->getData();

            $user->setPseudo($data->getPseudo());
            $user->setFirstname($data->getFirstname());
            $user->setLastname($data->getLastname());
            $user->setBirthday($data->getBirthday());
            $em = $this->getDoctrine()->getManager(); // on récupère la gestion des entités
            $em->persist($user); // on effectue les mise à jours internes
            $em->flush(); // on effectue la mise à jour vers la base de données
            $this->addFlash('message', 'Profil mis à jour');

        }
        return $this->render('security/modify.html.twig', ['form' => $form->createView(),'user'=>$user,'pwd'=>$pwd->createView()]);

    }



}

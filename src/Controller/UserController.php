<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class UserController
 * @package App\Controller*
 * @Route("/profil")
 */

class UserController extends AbstractController
{
    /**
     * @Route("/", name="user_profil")
     */
    public function index(): Response
    {
        $user = $this->getUser();

        if(isset($_POST['avatar'])){
            $user->setAvatar($_POST['avatar']);
            $em = $this->getDoctrine()->getManager(); // on récupère la gestion des entités
            $em->persist($user); // on effectue les mise à jours internes
            $em->flush(); // on effectue la mise à jour vers la base de données
        }
        return $this->render('user/index.html.twig', [
            'user' => $user,
        ]);
    }
}

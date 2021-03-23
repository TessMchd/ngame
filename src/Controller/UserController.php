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


        return $this->render('user/index.html.twig', [
            'user' => $user
        ]);
    }
}

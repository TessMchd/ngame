<?php

namespace App\Controller;

use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Routing\Annotation\Route;

class DefaultController extends AbstractController
{
    /**
     * @Route("/default", name="default")
     */
    public function index(MailerInterface $mailer): Response

    {
        $email = (new TemplatedEmail())
            ->to('tessmchd@hotmail.fr')
            ->from('no_reply@ngame.com')
            ->subject('Test d un mail')
            ->text('Coucou')
            ->context([
            'prenom' => 'Tess',
            'nom' => 'Machado',
        ]);

        $mailer->send($email);
        return $this->render('default/index.html.twig', [
            'controller_name' => 'DefaultController',
        ]);
    }
}

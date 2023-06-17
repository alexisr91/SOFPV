<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class AdminAccountController extends AbstractController
{
    // connexion administrateur
    #[Route('/admin/login', name: 'admin_login')]
    public function login(AuthenticationUtils $utils): Response
    {
        $error = $utils->getLastAuthenticationError();
        $username = $utils->getLastUsername();

        return $this->render('admin/account/login.html.twig', [
            'title' => 'Connexion administateur',
            'error' => null !== $error,
            'username' => $username,
        ]);
    }

    // déconnexion admin
    #[Route('admin/logout', name: 'admin_logout')]
    public function logout(): void
    {
    }
}

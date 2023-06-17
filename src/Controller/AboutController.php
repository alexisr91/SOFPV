<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class AboutController extends AbstractController
{
    // page "à propos"
    // "about" of SO FPV
    #[Route('/about', name: 'about')]
    public function index(): Response
    {
        return $this->render('about/index.html.twig', [
            'title' => 'A propos',
        ]);
    }
}

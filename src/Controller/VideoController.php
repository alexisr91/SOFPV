<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class VideoController extends AbstractController
{
    #[Route('/profile/video', name: 'profile_video')]
    public function index(): Response
    {
        // $ffmpeg = $this->initFfmpeg();

        return $this->render('account/video/index.html.twig', [
            'controller_name' => 'VideoController',
        ]);
    }
}

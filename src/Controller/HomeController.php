<?php

namespace App\Controller;

use App\Repository\ArticleRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class HomeController extends AbstractController
{
    #[Route('/', name: 'home')]
    public function index(ArticleRepository $articleRepo): Response
    {

        //4 derniers articles SAUF l'article à la une
        $articles = $articleRepo->findLastArticles();

        //le dernier article à la une coché par l'admin
        $adminNews = $articleRepo->findAdminNewsArticle();

        return $this->render('home/index.html.twig', [
            'title' => 'Bienvenue sur SO FPV !',
            'articles'=>$articles,
            'adminNews'=>$adminNews[0]
        ]);
    }
}

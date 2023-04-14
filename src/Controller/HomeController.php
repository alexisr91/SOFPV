<?php

namespace App\Controller;

use App\Repository\ArticleRepository;
use App\Repository\CounterRepository;
use App\Repository\ProductRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class HomeController extends AbstractController
{
    #[Route('/', name: 'home')]
    public function index(ArticleRepository $articleRepo, CounterRepository $counterRepo, ProductRepository $productRepo): Response
    {

        //4 derniers articles SAUF l'article à la une
        $articles = $articleRepo->findLastArticles();

        //le dernier article à la une coché par l'admin
        $adminNews = $articleRepo->findAdminNewsArticle();

        //le compte de lipo, d'esc et de frames brisés pour l'animation de la page d'accueil
        $counterLipo = $counterRepo->countLipo();
        $counterESC = $counterRepo->countESC();
        $counterFrame = $counterRepo->countFrame();

        //les 4 produits les plus récents
        $products = $productRepo->findLastFourProducts();
        
        return $this->render('home/index.html.twig', [
            'title' => 'Bienvenue sur SO FPV !',
            'articles'=>$articles,
            'adminNews'=>$adminNews[0],
            'counterLipo'=>$counterLipo,
            'counterESC'=>$counterESC,
            'counterFrame'=>$counterFrame,
            'products'=>$products
        ]);
    }
    
    
}

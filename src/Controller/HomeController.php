<?php

namespace App\Controller;

use App\Repository\ArticleRepository;
use App\Repository\CounterRepository;
use App\Repository\MapSpotRepository;
use App\Repository\ProductRepository;
use App\Repository\SessionRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class HomeController extends AbstractController
{
    // home page
    #[Route('/', name: 'home')]
    public function index(EntityManagerInterface $manager, ArticleRepository $articleRepo, MapSpotRepository $mapSpotRepository, CounterRepository $counterRepo, ProductRepository $productRepo, SessionRepository $sessionRepository): Response
    {
        $active = true;
        $adminNews = false;
        // 4 derniers articles SAUF l'article à la une - last 4 publication without "A la Une" publication
        $articles = $articleRepo->findLastArticles($adminNews, $active);

        // le dernier article à la une coché par l'admin - last "A la Une" admin publication
        $isAdminNews = true;
        $adminNews = $articleRepo->findAdminNewsArticle($isAdminNews);

        // le compte de lipo, d'esc et de frames brisés pour l'animation de la page d'accueil
        // counter for lipo, esc and frame broken for home page animation
        $lipo = 'Lipo';
        $esc = 'ESC';
        $frame = 'Frame';

        $counterLipo = $counterRepo->counter($lipo);
        $counterESC = $counterRepo->counter($esc);
        $counterFrame = $counterRepo->counter($frame);

        // les 4 produits les plus récents - last 4 newer products
        $products = $productRepo->findLastFourProducts($active);

        // les 6 dernières sessions ajoutées encore actives - 6 lasts active sessions
        $sessions = $sessionRepository->findLastSessions();

        // on met à jour les sessions pour voir si elles sont dejà passées ou non
        // update for flight sessions

        foreach ($sessions as $session) {
            // si certaines le sont, on envoie past  = true a la bdd pour qu'elles n'apparaissent plus
            // if there are past session, update on database
            if ($session->isAlreadyPast()) {
                $session->setPast(true);
                $manager->persist($session);
                $manager->flush();
            }
        }

        // les spots(points) ajoutés par l'admin sur lesquels des sessions peuvent etre ajoutées
        // map spot added by admin where flight sessions can be added by users
        $mapSpots = $mapSpotRepository->findAll();

        //le dernier article à la une coché par l'admin
        $adminNews = $articleRepo->findAdminNewsArticle();

        //le compte de lipo, d'esc et de frames brisés pour l'animation de la page d'accueil
        $counterLipo = $counterRepo->countLipo();
        $counterESC = $counterRepo->countESC();
        $counterFrame = $counterRepo->countFrame();

        //les 4 produits les plus récents
        $products = $productRepo->findLastFourProducts();
        
        //les 5 dernières sessions ajoutées
        $sessions = $sessionRepository->findLastSessions();

        //les spots(points) ajoutés par l'admin sur lesquels des sessions peuvent etre ajoutées
        $mapSpots = $mapSpotRepository->findAll();

        return $this->render('home/index.html.twig', [
            'title' => 'Bienvenue sur SO FPV !',
            'articles' => $articles,
            'adminNews' => $adminNews[0],
            'counterLipo' => $counterLipo,
            'counterESC' => $counterESC,
            'counterFrame' => $counterFrame,
            'products' => $products,
            'sessions' => $sessions,
            'mapSpots' => $mapSpots,
        ]);
    }
    
    
}

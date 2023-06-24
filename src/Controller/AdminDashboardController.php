<?php

namespace App\Controller;

use App\Repository\OrderRepository;
use App\Repository\ProductRepository;
use App\Services\Stats;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class AdminDashboardController extends AbstractController
{
    #[Route('/admin/dashboard', name: 'admin_dashboard')]
    public function dashboard(Stats $statsService, OrderRepository $orderRepository, ProductRepository $productRepository): Response
    {
        // call statService to fill adminDashboard
        // appel du service pour récupérer les statistiques pour le tableau de bord
        $stats = $statsService->getStats();

        // most liked publications (based on views and likes)
        // les articles les + appréciés
        $mostLikedArticles = $statsService->getArticlesStats();

        // most active users ( publications, sessions )
        // utilisateurs les + actifs
        $mostActiveUsers = $statsService->getUsersStats();

        // user to watch for bad content ( alerted on comments or publications )
        // utilisateurs les plus signalés (commentaires et articles)
        $worstUsers = $statsService->getWorstUsers();

        // status = 0 => order to prepare (for admin shortcut)
        $status = 0;

        // amount of orders to prepare
        $ordersToPrepare = count($orderRepository->findOrderToMake($status));

        // amount of actives products which no longer in stock (shortcut)
        $outOfStock = count($productRepository->getProductsOutOfStock());

        $status = 0;
        $ordersToPrepare = count($orderRepository->findOrderToMake($status)) ;
        $outOfStock = count($productRepository->getProductsOutOfStock());


        return $this->render('admin/dashboard/index.html.twig', [
            'title' => 'Tableau de bord de l\'administrateur',
            'stats' => $stats,
            'mostLikedArticles' => $mostLikedArticles,
            'mostActiveUsers' => $mostActiveUsers,
            'worstUsers' => $worstUsers,
            'ordersToPrepare' => $ordersToPrepare,
            'outOfStock' => $outOfStock,
        ]);
    }
}

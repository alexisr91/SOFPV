<?php

namespace App\Controller;

use App\Services\Stats;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class AdminDashboardController extends AbstractController
{

    #[Route('/admin/dashboard', name: 'admin_dashboard')]
    public function dashboard(Stats $statsService): Response
    {
        //appel du service pour récupérer les statistiques pour le tableau de bord
        $stats = $statsService->getStats();

        //les articles les + appréciés
        $mostLikedArticles = $statsService->getArticlesStats();
        $mostActiveUsers = $statsService->getUsersStats();


        return $this->render('admin/dashboard/index.html.twig', [
            'title' => 'Tableau de bord de l\'administrateur',
            'stats'=>$stats,
            'mostLikedArticles'=>$mostLikedArticles,
            'mostActiveUsers'=>$mostActiveUsers
        ]);
    }


}

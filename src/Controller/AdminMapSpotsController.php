<?php

namespace App\Controller;

use App\Entity\MapSpot;
use App\Repository\MapSpotRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class AdminMapSpotsController extends AbstractController
{
    //gestion des spots pour la carte d'organisation des sessions
    #[Route('/admin/map/spots', name: 'admin_spots')]
    public function index(MapSpotRepository $mapSpotRepository): Response
    {
        $spots = $mapSpotRepository->findAll();
        return $this->render('admin/mapSpots/index.html.twig', [
            'title' => 'Gestion de la carte et des spots',
            'spots'=>$spots
        ]);
    }
}

<?php

namespace App\Controller;

use App\Form\SpotType;
use App\Entity\MapSpot;
use App\Repository\MapSpotRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class AdminMapSpotsController extends AbstractController
{
    //gestion des spots pour la carte d'organisation des sessions
    #[Route('/admin/map/spots', name: 'admin_spots')]
    public function index(Request $request, EntityManagerInterface $manager, MapSpotRepository $mapSpotRepository): Response
    {
        $newSpot = new MapSpot();
        $form = $this->createForm(SpotType::class, $newSpot);

        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()){
            $manager->persist($newSpot);
            $manager->flush();

            $this->addFlash('success', 'Le spot a été ajouté avec succès.');
            $this->redirectToRoute('admin_spots');
        }

        $spots = $mapSpotRepository->findAll();

        return $this->render('admin/mapSpots/index.html.twig', [
            'title' => 'Gestion de la carte et des spots',
            'spots'=>$spots,
            'form'=> $form->createView()
        ]);
    }
}

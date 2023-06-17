<?php

namespace App\Controller;

use App\Form\SpotType;
use App\Entity\MapSpot;
use App\Form\SpotType;
use App\Repository\MapSpotRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class AdminMapSpotsController extends AbstractController
{
    // spot gestion to organize flight session : list of map spot and form to add a spot
    // gestion des spots pour la carte d'organisation des sessions
    #[Route('/admin/map/spots', name: 'admin_spots')]
    public function index(Request $request, EntityManagerInterface $manager, MapSpotRepository $mapSpotRepository): Response
    {
        $newSpot = new MapSpot();
        $form = $this->createForm(SpotType::class, $newSpot);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $manager->persist($newSpot);
            $manager->flush();

            $this->addFlash('success', 'Le spot a été ajouté avec succès.');

            return $this->redirectToRoute('admin_spots');
        }

        $spots = $mapSpotRepository->findAll();

        return $this->render('admin/mapSpots/index.html.twig', [
            'title' => 'Gestion de la carte et des spots',
            'spots' => $spots,
            'form' => $form->createView(),
        ]);
    }

    // delete a spot
    // suppression d'un spot
    #[Route('admin/map/spot/delete/{id}', name: 'admin_spot_delete')]
    public function delete(EntityManagerInterface $manager, MapSpotRepository $mapSpotRepository, int $id, Request $request): Response
    {
        $token = $request->request->get('token');

        if ($this->isCsrfTokenValid('delete', $token)) {
            $spot = $mapSpotRepository->findOneBy(['id' => $id]);
            if ($spot) {
                try {
                    $manager->remove($spot);
                    $manager->flush();

                    $this->addFlash('success', 'Le spot a bien été supprimé.');

                    return $this->redirectToRoute('admin_spots');
                } catch (\Exception $e) {
                    $this->addFlash('danger', 'Vous ne pouvez pas supprimer un spot sur lequel il y a des sessions prévues.');

                    return $this->redirectToRoute('admin_spots');
                }
            } else {
                $this->addFlash('danger', 'Le spot que vous tentez de supprimer n\'existe pas.');

                return $this->redirectToRoute('admin_spots');
            }

            return $this->redirectToRoute('admin_spots');
        } else {
            throw new BadRequestHttpException();
        }

    }
}

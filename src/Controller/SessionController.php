<?php

namespace App\Controller;

use App\Entity\Session;
use App\Form\SessionType;
use App\Repository\UserRepository;
use App\Repository\MapSpotRepository;
use App\Repository\SessionRepository;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Validator\Constraints\Timezone;

class SessionController extends AbstractController
{
    //carte des points de vol
    #[Route('/session/map', name: 'session_map')]
    public function index(MapSpotRepository $mapSpotRepository): Response
    {
        $spots = $mapSpotRepository->findAll();

        return $this->render('session/index.html.twig', [
            'title'=>'Carte des sessions',
            'spots'=>$spots
        ]);

    }

    //ajout d'une session
    #[IsGranted('ROLE_USER')]
    #[Route('/session/add/{id}', name:'session_add')]
    public function addSession(MapSpotRepository $mapSpotRepository, SessionRepository $sessionRepository, EntityManagerInterface $manager, Request $request, $id){

        $user = $this->getUser();

        $session = new Session; 

        $form = $this->createForm(SessionType::class, $session);
        $form->handleRequest($request); 
        
        $mapSpot= $mapSpotRepository->find($id);

        if($form->isSubmitted()&& $form->isValid()){
    
            //si le point existe bien
            if($mapSpot){

                $date = $form->get('date')->getData();
                $timeSheet = $form->get('timesheet')->getData();

                //on vérifie si il existe déjà une session sur ce MapSpot, avec la même heure et le même créneau horaire
                $sameSessionOnDb = $sessionRepository->isSessionAlreadyExist($id, $date, $timeSheet);

                //si il y en a une, on redirige vers la page des sessions avec un flash
                if($sameSessionOnDb !== null ){
                    $this->addFlash('danger','Cette session existe déjà : veuillez-vous inscrire sur la session existante !');
                    return $this->redirectToRoute('session_map');
                }

                //sinon on ajoute la session
                $session->addUser($user)
                        ->setMapSpot($mapSpot);
                $manager->persist($session);
                $manager->flush();  
                
                $this->addFlash('success', 'Votre session a bien été ajoutée ! Vous y êtes automatiquement inscrit.');
                return $this->redirectToRoute('session_map');

            } else {
                $this->addFlash('danger', 'Le spot est introuvable, la session n\'a pas pu être créée.');
                return $this->redirectToRoute('session_map');
            }   
        }

        return $this->render('session/sessionAdd.html.twig', [
            'title'=>'Ajout d\'une session',
            'form'=>$form->createView(),
            'mapSpot'=>$mapSpot
        ]);

    }

    public function isSessionAlreadyExist($session){

    }

    //inscription à une session existante
    #[IsGranted("ROLE_USER")]
    #[Route('/session/entry/{id}', name:'session_entry')]
    public function subUserToASession(UserRepository $userRepository, Session $session, EntityManagerInterface $manager){

        $user = $this->getUser();
        //on récupères les users inscrits à cette session
        $checkUserOnSession = $userRepository->findIfAlreadyRegisteredOnSession($session);

        if(!in_array($user, $checkUserOnSession)){
            //si l'user n'est pas dans la liste, on l'ajoute à la session
            $session->addUser($user);
            $manager->persist($session);
            $manager->flush();

            $this->addFlash('success','Votre inscription à la session est bien enregistrée !');
            return $this->redirectToRoute('session_map');
            //sinon on lui envoie un flash pour le prévenir qu'il est déja inscrit
        } else {
            $this->addFlash('danger', 'Vous êtes déjà inscrit à cette session.');
            return $this->redirectToRoute('session_map');
        }
        
      
        
    }
}

<?php

namespace App\Controller;

use App\Entity\User;
use App\Entity\Session;
use App\Form\SessionType;
use App\Repository\UserRepository;
use App\Repository\MapSpotRepository;
use App\Repository\SessionRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class SessionController extends AbstractController
{
    // carte des points de vol
    // map of flight spots
    #[Route('/session/map', name: 'session_map')]
    public function index(MapSpotRepository $mapSpotRepository, SessionRepository $sessionRepository, EntityManagerInterface $manager): Response
    {
        $spots = $mapSpotRepository->findAll();

        $checkSessions = $sessionRepository->findAll();
        // vérification et update pour voir si les sessions sont déjà passées
        // check if flight sessions are already past, if it's true, update it

        foreach ($checkSessions as $session) {
            // si elles le sont, on envoie en bdd past = true pour qu'elles n'apparaissent plus
            // if it's true, update "past" on true for hiding them
            if ($session->isAlreadyPast()) {
                $session->setPast(true);
                $manager->persist($session);
                $manager->flush();
            }
        }

        return $this->render('session/index.html.twig', [
            'title' => 'Carte des sessions',
            'spots' => $spots,
        ]);

    }

    // add a flight session
    // ajout d'une session de vol
    #[IsGranted('ROLE_USER')]
    #[Route('/session/add/{id}', name: 'session_add')]
    public function addSession(MapSpotRepository $mapSpotRepository, SessionRepository $sessionRepository, EntityManagerInterface $manager, Request $request, int $id) : Response
    {
        /** @var User $user */
        $user = $this->getUser();

        $session = new Session();

        $form = $this->createForm(SessionType::class, $session);
        $form->handleRequest($request);

        $mapSpot = $mapSpotRepository->find($id);

        if ($form->isSubmitted() && $form->isValid()) {
            // if the spot exists
            // si le point existe bien
            if ($mapSpot) {
                $date = $form->get('date')->getData();
                $timeSheet = $form->get('timesheet')->getData();

                // check if a flight session exist on the map spot, with the same hour and same timesheet
                // on vérifie si il existe déjà une session de vol sur ce MapSpot, avec la même heure et le même créneau horaire
                $sameSessionOnDb = $sessionRepository->isSessionAlreadyExist($id, $date, $timeSheet);

                // if there is a flight session, we redirect on flight sessions page with a toast
                // si il y en a une, on redirige vers la page des sessions avec un flash
                if (null !== $sameSessionOnDb) {
                    $this->addFlash('danger', 'Cette session existe déjà : veuillez-vous inscrire sur la session existante !');

                    return $this->redirectToRoute('session_map');
                }
                // else we add the flight session on database
                // sinon on ajoute la session à la base de données
                $session->addUser($user)
                        ->setMapSpot($mapSpot)
                        ->setPast(false);
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
            'title' => 'Ajout d\'une session',
            'form' => $form->createView(),
            'mapSpot' => $mapSpot,
        ]);
    }

    // subscription to a session
    // inscription à une session existante
    #[IsGranted('ROLE_USER')]
    #[Route('/session/entry/{id}', name: 'session_entry')]
    public function subUserToASession(UserRepository $userRepository, Session $session, EntityManagerInterface $manager) : Response
    {
         /** @var User $user */
        $user = $this->getUser();

        // get users list who are registered on a session
        // récupére la liste des inscrits à une session
        $checkUserOnSession = $userRepository->findIfAlreadyRegisteredOnSession($session);

       // check if user is already subscribed to the flight session by list subscribed users
        if (!in_array($user, $checkUserOnSession)) {
            // if the user is not in the list, we add him on flight session
            // si l'user n'est pas dans la liste, on l'ajoute à la session
            $session->addUser($user);
            $manager->persist($session);
            $manager->flush();

            $this->addFlash('success', 'Votre inscription à la session est bien enregistrée !');

            return $this->redirectToRoute('session_map');

            // sinon on lui envoie un flash pour le prévenir qu'il est déja inscrit
            // else we send a toast with a flash message to inform him he's already subscribed to it
        } else {
            $this->addFlash('danger', 'Vous êtes déjà inscrit à cette session.');

            return $this->redirectToRoute('session_map');
        }
    }


}

<?php

namespace App\Controller;

use App\Entity\User;
use App\Services\Pagination;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Routing\Annotation\Route;

class AdminUsersController extends AbstractController
{
    // user list with pagination service
    // index de tous les utilisateurs inscrits
    #[Route('/admin/users/{page<\d+>?1}', name: 'admin_users')]
    public function index(Pagination $paginationService, UserRepository $userRepo, Request $request, int $page): Response
    {
        // take request from search bar
        $q = $request->query->get('q');
        $active = true;

        // search users who are activated , by nickname
        $requestedUsers = $userRepo->findByPseudo($q, $active);

        // pagination service ( entity + current page + result limit + order)
        $pagination = $paginationService
            ->setEntityClass(User::class)
            ->setPage($page)
            ->setLimit(10)
            ->setOrder('DESC');

        return $this->render('admin/users/index.html.twig', [
            'title' => 'Gestion des utilisateurs',
            'pagination' => $pagination,
            'requestedUsers' => $requestedUsers,
        ]);
    }

    // user desactivation (stay in database, but he lost his access to his account. Can't reuse his mail or his nickname on registering)
    // désactivation de l'user (reste dans la bdd mais ne peut plus se connecter, ni réutiliser son adresse mail/pseudo)
    #[Route('admin/user/desactivate/{id}', name: 'admin_user_desactivate')]
    public function desactivate(EntityManagerInterface $manager, UserRepository $userRepository, int $id, Request $request): Response
    {
        $token = $request->request->get('token');
        $user = $userRepository->findOneBy(['id' => $id]);
        
        // vérification du token
        if ($this->isCsrfTokenValid('desactivate'.$user->getId(), $token)) {
            if ($user) {
                $user->setActive(false);
                $manager->persist($user);
                $manager->flush();

                $this->addFlash('success', 'L\'utilisateur a bien été désactivé.');
            } else {
                $this->addFlash('danger', 'L\'utilisateur n\'existe pas.');
            }

            return $this->redirectToRoute('admin_users');
        } else {
            // renvoi sur une page d'erreur
            throw new BadRequestHttpException();
        }
    }

    // re activate an user account
    // activation de l'user
    #[Route('admin/user/activate/{id}', name: 'admin_user_activate')]
    public function activate(EntityManagerInterface $manager, UserRepository $userRepository, int $id, Request $request): Response
    {
        $token = $request->request->get('token');
        $user = $userRepository->findOneBy(['id' => $id]);

        // vérification du token
        if ($this->isCsrfTokenValid('activate'.$user->getId(), $token)) {

            if ($user) {
                $user->setActive(true);
                $manager->persist($user);
                $manager->flush();

                $this->addFlash('success', 'L\'utilisateur a bien été activé.');
            } else {
                $this->addFlash('danger', 'L\'utilisateur n\'a pas pu être activé.');
            }

            return $this->redirectToRoute('admin_users');
        } else {
            throw new BadRequestHttpException();
        }
    }

    // delete all the account data from the user
    // supprime entièrement le compte utilisateur (RGPD)
    #[Route('admin/user/delete/{id}', name: 'admin_user_delete')]
    public function delete(EntityManagerInterface $manager, UserRepository $userRepository, int $id, Request $request): Response
    {
        $token = $request->request->get('token');
        $user = $userRepository->findOneBy(['id' => $id]);

        // vérification du token
        if ($this->isCsrfTokenValid('delete'.$user->getId(), $token)) {
            $manager->remove($user);
            $manager->flush();

            $this->addFlash('success', 'L\'utilisateur et ses données ont bien été supprimés.');

            return $this->redirectToRoute('admin_users');
        } else {
            throw new BadRequestHttpException();
        }
    }

    //delete all the account data from the user
    //supprime entièrement le compte utilisateur (RGPD)
    #[Route('admin/user/delete/{id}' , name:'admin_user_delete')]
    public function delete(EntityManagerInterface $manager, UserRepository $userRepository, $id, Request $request){

        $token = $request->request->get('token');
        $user = $userRepository->findOneBy(['id'=>$id]);

        //vérification du token
        if($this->isCsrfTokenValid('delete'. $user->getId(), $token)){

            $manager->remove($user);
            $manager->flush();

            $this->addFlash('success','L\'utilisateur et ses données ont bien été supprimés.');
            return $this->redirectToRoute('admin_users');

        } else {
            throw new BadRequestHttpException();
        }
    }
}

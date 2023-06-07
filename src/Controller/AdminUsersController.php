<?php

namespace App\Controller;

use App\Entity\User;
use App\Services\Pagination;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class AdminUsersController extends AbstractController
{
    //index de tous les utilisateurs inscrits
    #[Route('/admin/users/{page<\d+>?1}', name: 'admin_users')]
    public function index(Pagination $paginationService, UserRepository $userRepo, Request $request, $page): Response
    {
        $q = $request->query->get('q');

        $requestedUsers = $userRepo->findByPseudo($q);

        $pagination = $paginationService
            ->setEntityClass(User::class)
            ->setPage($page)
            ->setLimit(10)
            ->setOrder('DESC');
            

        return $this->render('admin/users/index.html.twig', [
            'title' => 'Gestion des utilisateurs',
            'pagination'=>$pagination,
            'requestedUsers'=>$requestedUsers
        ]);
    }

    //désactivation de l'user (reste dans la bdd mais ne peut plus se connecter, ni réutiliser son adresse mail/pseudo)
    #[Route('admin/user/desactivate/{id}' , name:'admin_user_desactivate')]
    public function desactivate(EntityManagerInterface $manager, UserRepository $userRepository, $id){
    
        $user = $userRepository->findOneBy(['id'=>$id]);
        if($user){
            $user->setActive(false);
            $manager->persist($user);
            $manager->flush();
            
            $this->addFlash('success','L\'utilisateur a bien été désactivé.');
        
        } else {
            $this->addFlash('danger','L\'utilisateur n\'existe pas.');
        }
    
        return $this->redirectToRoute('admin_users');
    }

    //activation de l'user
    #[Route('admin/user/activate/{id}' , name:'admin_user_activate')]
    public function activate(EntityManagerInterface $manager, UserRepository $userRepository, $id){
    
        $user = $userRepository->findOneBy(['id'=>$id]);
        if($user){
            $user->setActive(true);
            $manager->persist($user);
            $manager->flush();
            
            $this->addFlash('success','L\'utilisateur a bien été activé.');
        
        } else {
            $this->addFlash('danger','L\'utilisateur n\'a pas pu être activé.');
        }
    
        return $this->redirectToRoute('admin_users');
    }
}

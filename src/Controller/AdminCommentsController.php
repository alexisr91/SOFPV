<?php

namespace App\Controller;

use App\Repository\CommentRepository;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\AlertCommentRepository;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class AdminCommentsController extends AbstractController
{
    //liste des commentaires signalés
    #[Route('/admin/comments', name: 'admin_comments')]
    public function index(AlertCommentRepository $alertCommentRepository): Response
    {
        $alerts = $alertCommentRepository->findBy([],["createdAt"=>"DESC"]);

        return $this->render('admin/comments/index.html.twig', [
            'title'=>'Gestion des commentaires',
            'alerts'=>$alerts
        ]);
    }

    #[Route('admin/comment/moderate/{id}', name:'admin_comment_moderate')]
    public function moderate(EntityManagerInterface $manager, CommentRepository $commentRepo, $id){

       $comment = $commentRepo->find($id);
       if($comment){
            $comment->setContent("<i>"."Ce commentaire a été modéré."."</i>");
            $manager->persist($comment);
            $manager->flush();

            $this->addFlash('success','Le commentaire a été modéré avec succès.');
            return $this->redirectToRoute('admin_comments');
       } else {
            $this->addFlash('danger','Le commentaire n\'existe plus.');
            return $this->redirectToRoute('admin_comments');
       }

    }
}

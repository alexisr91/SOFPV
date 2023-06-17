<?php

namespace App\Controller;

use App\Repository\AlertCommentRepository;
use App\Repository\CommentRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Routing\Annotation\Route;

class AdminCommentsController extends AbstractController
{
    // list of alerted comments
    // liste des commentaires signalés
    #[Route('/admin/comments', name: 'admin_comments')]
    public function index(AlertCommentRepository $alertCommentRepository): Response
    {
        $alerts = $alertCommentRepository->findBy([], ['createdAt' => 'DESC']);

        return $this->render('admin/comments/index.html.twig', [
            'title' => 'Gestion des commentaires',
            'alerts' => $alerts,
        ]);
    }

    // moderation
    #[Route('admin/comment/moderate/{id}', name: 'admin_comment_moderate')]
    public function moderate(EntityManagerInterface $manager, CommentRepository $commentRepo, int $id, Request $request): Response
    {
        $token = $request->request->get('token');

        // vérification du token
        if ($this->isCsrfTokenValid('moderate', $token)) {
            $comment = $commentRepo->find($id);
            // si le commentaire correspond bien à l'id récupéré
            if ($comment) {
                $comment->setContent('<i>Ce commentaire a été modéré.</i>');
                $manager->persist($comment);
                $manager->flush();

                $this->addFlash('success', 'Le commentaire a été modéré avec succès.');

                return $this->redirectToRoute('admin_comments');
            } else {
                $this->addFlash('danger', 'Le commentaire n\'existe plus.');

                return $this->redirectToRoute('admin_comments');
            }
        } else {
            throw new BadRequestHttpException();
        }
    }
}

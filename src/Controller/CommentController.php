<?php

namespace App\Controller;

use App\Entity\Comment;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Routing\Annotation\Route;

class CommentController extends AbstractController
{
    // suppression de notre propre commentaire sur un article - delete our comment on an article
    // vérification en amont avec Security() pour vérifier que l'auteur est bien celui qui supprime le commentaire
    #[Route('/comment/{id}/delete', name: 'comment_delete')]
    #[Security("is_granted('ROLE_USER') and user == comment.getAuthor()", message: "Vous n'avez pas le droit de supprimer ce commentaire.")]
    public function delete(Comment $comment, EntityManagerInterface $manager, Request $request) : RedirectResponse
    {
        // récupération des données du formulaire de token - get data form token form
        $token = $request->request->get('token');

        // si le token est valide - token valid
        if ($this->isCsrfTokenValid('commentDelete'.$comment->getId(), $token)) {
            // suppression du commentaire
            // delete comment
            $manager->remove($comment);
            $manager->flush();

            // toast
            $this->addFlash('success', 'Votre commentaire a bien été supprimé.');

            // redirection vers la page de l'article où le commentaire a été supprimé - redirection to article where it has been deleted
            return $this->redirectToRoute('article_show', ['slug' => $comment->getArticle()->getSlug()]);
        } else {
            throw new BadRequestHttpException();
        }
    }
}

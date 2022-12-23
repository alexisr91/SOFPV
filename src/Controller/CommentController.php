<?php

namespace App\Controller;

use App\Entity\Comment;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class CommentController extends AbstractController
{   
    #[Route('/comment/{id}/delete', name: 'comment_delete')]
    #[Security("is_granted('ROLE_USER') and user == comment.getAuthor()", message:"Vous n'avez pas le droit de supprimer ce commentaire.")]
    public function delete(Comment $comment, EntityManagerInterface $manager)
    {
       //vérification en amont avec Security() pour vérifier que l'auteur est bien celui qui supprime le commentaire
       $manager->remove($comment);
       $manager->flush();
       
       $this->addFlash('success','Votre commentaire a bien été supprimé.');
       //redirection vers la page de l'article où le commentaire a été supprimé
       return $this->redirectToRoute('article_show', ['slug'=> $comment->getArticle()->getSlug()]);
    }
}

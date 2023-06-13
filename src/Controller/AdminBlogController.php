<?php

namespace App\Controller;

use App\Entity\Alert;
use App\Entity\Article;
use App\Services\Pagination;
use App\Repository\AlertRepository;
use App\Repository\ArticleRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class AdminBlogController extends AbstractController
{
    //index des articles avec possibilité de les désactiver + vue sur les stats (nombre de signalements, de vues, de likes et de commentaires)
    #[Route('/admin/blog/{page<\d+>?1}', name: 'admin_blog')]
    public function index(Request $request, ArticleRepository $articleRepo, Pagination $paginationService, $page): Response
    {
        $q = $request->query->get('q');
        $requestedArticles = $articleRepo->findByTitle($q);

        $pagination = $paginationService
                    ->setEntityClass(Article::class)
                    ->setPage($page)
                    ->setLimit(8)
                    ->setOrder("DESC");
                    ;


        return $this->render('admin/blog/index.html.twig', [
            'title' => 'Gestion des articles',
            'pagination'=>$pagination,
            'requestedArticles'=>$requestedArticles
        ]);
    }

    //désactiver un article
    #[Route('admin/blog/desactivate/{id}', name:'admin_blog_desactivate')]
    public function desactivate(EntityManagerInterface $manager, ArticleRepository $articleRepo, $id){

        $article = $articleRepo->findOneBy(['id'=>$id]);

        if($article){
            $article->setActive(false);
            $manager->persist($article);
            $manager->flush();

            $this->addFlash('success','L\'article a bien été désactivé.');
        
            } else {
                $this->addFlash('danger','L\'article n\'existe pas.');
        }
    
        return $this->redirectToRoute('admin_blog');
    }

    //activer un article
    #[Route('admin/blog/activate/{id}', name:'admin_blog_activate')]
    public function activate(EntityManagerInterface $manager, ArticleRepository $articleRepo, $id){

        $article = $articleRepo->findOneBy(['id'=>$id]);

        if($article){
            $article->setActive(true);
            $manager->persist($article);
            $manager->flush();

            $this->addFlash('success','L\'article a bien été activé.');
        
        } else {
            $this->addFlash('danger','L\'article n\'a pas pu être activé.');
        }
    
        return $this->redirectToRoute('admin_blog');
    }

}

<?php

namespace App\Controller;

use App\Entity\Article;
use App\Repository\ArticleRepository;
use App\Services\Pagination;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Routing\Annotation\Route;

class AdminBlogController extends AbstractController
{
    // index des articles avec possibilité de les désactiver + vue sur les stats (nombre de signalements, de vues, de likes et de commentaires)
    // list of publications with the possibility to desactivate them + view on stats (amount of alerts, views, likes and comments)
    #[Route('/admin/blog/{page<\d+>?1}', name: 'admin_blog')]
    public function index(Request $request, ArticleRepository $articleRepo, Pagination $paginationService, int $page): Response
    {
        // request for finding publication by title, only for active publications
        $active = true;
        // take value wrote on search bar
        $q = $request->query->get('q');
        // request form repository to find article by title, which is active
        $requestedArticles = $articleRepo->findByTitle($q, $active);

        // pagination service : need entity, current page, limit of results and type of order. Can take optionals value/parameters.
        $pagination = $paginationService
                    ->setEntityClass(Article::class)
                    ->setPage($page)
                    ->setLimit(8)
                    ->setOrder('DESC');

        return $this->render('admin/blog/index.html.twig', [
            'title' => 'Gestion des articles',
            'pagination' => $pagination,
            'requestedArticles' => $requestedArticles,
        ]);
    }

    // only publications wich have alerts
    // uniquement les articles signalés
    #[Route('admin/blog/alerts', name: 'admin_blog_alerts')]
    public function alertedArticles(ArticleRepository $articleRepo): Response
    {
        $articles = $articleRepo->findAlertedArticles();

        return $this->render('admin/blog/blogAlerts.html.twig', [
            'title' => 'Gestion des articles signalés',
            'articles' => $articles,
        ]);
    }

    // desactivate a publication
    // désactiver un article
    #[Route('admin/blog/desactivate/{id}', name: 'admin_blog_desactivate')]
    public function desactivate(EntityManagerInterface $manager, ArticleRepository $articleRepo, int $id, Request $request): Response
    {
        $token = $request->request->get('token');

        // vérification du token
        if ($this->isCsrfTokenValid('desactivate', $token)) {
            $article = $articleRepo->findOneBy(['id' => $id]);

            if ($article) {
                $article->setActive(false);
                $manager->persist($article);
                $manager->flush();

                $this->addFlash('success', 'L\'article a bien été désactivé.');
            } else {
                $this->addFlash('danger', 'L\'article n\'existe pas.');
            }

            return $this->redirectToRoute('admin_blog');
            // si le token ne correspond pas
        } else {
            throw new BadRequestHttpException();
        }
    }

    // activate a publication
    // activer un article
    #[Route('admin/blog/activate/{id}', name: 'admin_blog_activate')]
    public function activate(EntityManagerInterface $manager, ArticleRepository $articleRepo, int $id, Request $request) : Response
    {
        // check token to avoid csrf manipulation
        $token = $request->request->get('token');

        // vérification du token
        if ($this->isCsrfTokenValid('activate', $token)) {
            $article = $articleRepo->findOneBy(['id' => $id]);

            if ($article) {
                $article->setActive(true);
                $manager->persist($article);
                $manager->flush();

                $this->addFlash('success', 'L\'article a bien été activé.');
            } else {
                $this->addFlash('danger', 'L\'article n\'a pas pu être activé.');
            }

            return $this->redirectToRoute('admin_blog');
        } else {
            throw new BadRequestHttpException();
        }
    }
}

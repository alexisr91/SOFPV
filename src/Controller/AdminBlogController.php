<?php

namespace App\Controller;

use App\Entity\Article;
use App\Repository\ArticleRepository;
use App\Services\Pagination;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class AdminBlogController extends AbstractController
{
    #[Route('/admin/blog/{page<\d+>?1}', name: 'admin_blog')]
    public function index(Request $request, ArticleRepository $articleRepo, Pagination $paginationService, $page): Response
    {
        $q = $request->query->get('q');
        $requestedArticles = $articleRepo->findByTitle($q);

        $pagination = $paginationService
                    ->setEntityClass(Article::class)
                    ->setPage($page)
                    ->setLimit(10)
                    ->setOrder('DESC')
                    ;

        return $this->render('admin/blog/index.html.twig', [
            'title' => 'Gestion des articles',
            'pagination'=>$pagination,
            'requestedArticles'=>$requestedArticles
        ]);
    }
}

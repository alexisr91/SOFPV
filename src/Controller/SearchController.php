<?php

namespace App\Controller;

use App\Repository\ArticleRepository;
use App\Repository\CategoryRepository;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class SearchController extends AbstractController
{
    #[Route('/search', name: 'search')]
    public function index(ArticleRepository $articleRepo, UserRepository $userRepo, CategoryRepository $categoryRepo, Request $request): Response
    {
        // Résultat de ce qui est tapé en barre de recherche - get what's send in search bar
        $q = $request->query->get('q');

        // recherches uniquement sur les articles et utilisateurs actifs - active users and active publications only
        $active = true;

        // dissocie la recherche pour retrouver des articles, des profils user ou des catégories
        // dissociation of results to find publications, profiles or video with a category
        $articles = $articleRepo->findByTitle($q, $active);
        $users = $userRepo->findByPseudo($q, $active);
        $category = $categoryRepo->findByName($q, $active);


        return $this->render('search/index.html.twig', [
            'articles' => $articles,
            'users' => $users,
            'category' => $category,
            'request' => $q,
        ]);

        return $this->render('search/index.html.twig');
    }
}

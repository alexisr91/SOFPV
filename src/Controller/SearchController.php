<?php

namespace App\Controller;

use App\Repository\UserRepository;
use App\Repository\ArticleRepository;
use App\Repository\CategoryRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class SearchController extends AbstractController
{
    #[Route('/search', name: 'search')]
    public function index(ArticleRepository $articleRepo, UserRepository $userRepo, CategoryRepository $categoryRepo, Request $request): Response
    {
        //dd($request->query->get('q'));
        
        // Résultat de ce qui est tapé en barre de recherche
        $q = $request->query->get('q');

        //Pour avoir uniquement les videos correspondant à la recherche (via pseudo,titre ou categorie):
        //$result = $videoRepo->searchByCriteria($q); 

        //dissocie la recherche pour retrouver des articles, des profils user ou des catégories
        $articles = $articleRepo->findByTitle($q);
        $users = $userRepo->findByPseudo($q);
        $category = $categoryRepo->findByName($q);

        //dd($result);

        return $this->render('search/index.html.twig', [
            //'result'=> $result
            'articles'=>$articles,
            'users'=>$users,
            'category'=>$category,
            'request'=>$q
        ]);
        return $this->render('search/index.html.twig');
    }
}

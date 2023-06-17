<?php

namespace App\Services;

use App\Repository\ArticleRepository;
use App\Repository\OrderRepository;
use App\Repository\SessionRepository;
use App\Repository\UserRepository;

class Stats
{

    private $articleRepository;
    private $userRepository;
    private $sessionRepository;
    private $orderRepository;

    public function __construct(ArticleRepository $articleRepository, UserRepository $userRepository, SessionRepository $sessionRepository, OrderRepository $orderRepository)
    {
        $this->articleRepository = $articleRepository;
        $this->userRepository = $userRepository;
        $this->sessionRepository = $sessionRepository;
        $this->orderRepository = $orderRepository;

    }

    // nombre d'utilisateurs
    public function getUsersCount():int
    {
        $usersCount = $this->userRepository->getUsersCount();
        return $usersCount;
    }

    // nombre d'articles
    public function getArticlesCount():int
    {
        $articleCount = $this->articleRepository->getArticleCount();
       return $articleCount;
    }

    // nombre de sessions
    public function getSessionsCount():int
    {
       $sessionCount = $this->sessionRepository->getSessionsCount();
       return $sessionCount;
    }

    // nombre de commandes
    public function getOrdersCount():int
    {
        $orderCount = $this->orderRepository->getOrderCount();
        return $orderCount;
    }

    // récupération de toutes les stats pour les compacter dans le controller
    public function getStats():array
    {
        $users = $this->getUsersCount();
        $articles = $this->getArticlesCount();
        $sessions = $this->getSessionsCount();
        $orders = $this->getOrdersCount();

        return compact('users', 'articles', 'sessions', 'orders');
    }

    // récupération des articles les + vus par vues et likés (articles les + appréciés)
    public function getArticlesStats():array
    {
       $articleStats = $this->articleRepository->getMostLikedArticles();
       return $articleStats;
    }

    // récupération des utilisateurs les + actifs (articles, sessions puis commentaires)
    public function getUsersStats():array
    {
        $usersStats = $this->userRepository->getUsersStats();
        return $usersStats;
    }

    // récupération des utilisateurs les + signalés (commentaires et articles)
    public function getWorstUsers():array
    {
       $worstUsers = $this->userRepository->getWorstUsers();
       return $worstUsers;
    }
}

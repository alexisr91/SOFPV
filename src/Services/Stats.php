<?php

namespace App\Services;

use Doctrine\ORM\EntityManagerInterface;

class Stats {

    private $manager;

    public function __construct(EntityManagerInterface $manager) {
        $this->manager = $manager;
    }

    //nombre d'utilisateurs
    public function getUsersCount(){
        return $this->manager->createQuery('SELECT COUNT(u) FROM App\Entity\User u')->getSingleScalarResult();

    }

    //nombre d'articles
    public function getArticlesCount(){
        return $this->manager->createQuery('SELECT COUNT(a) FROM App\Entity\Article a')->getSingleScalarResult();
    }

    //nombre de sessions 
    public function getSessionsCount(){
        return $this->manager->createQuery('SELECT COUNT(s) FROM App\Entity\Session s')->getSingleScalarResult();
    }

    //nombre de commandes
    public function getOrdersCount(){
        return $this->manager->createQuery('SELECT COUNT(o) FROM App\Entity\Order o')->getSingleScalarResult();
    }


    //récupération de toutes les stats pour les compacter dans le controller
    public function getStats(){
        $users = $this->getUsersCount();
        $articles = $this->getArticlesCount();
        $sessions = $this->getSessionsCount();
        $orders = $this->getOrdersCount();

        return compact('users', 'articles', 'sessions', 'orders');
    }

    //récupération des articles les + vus par vues et likés (articles les + appréciés)
    public function getArticlesStats(){
        return $this->manager->createQuery(
        'SELECT a.title, a.slug , u.nickname , a.views, COUNT(l.article) as likes
        FROM App\Entity\Article a
        JOIN a.author u
        JOIN a.likes l
        GROUP BY a
        ORDER BY a.views DESC')
        ->setMaxResults(5)
        ->getResult();
    }
    
    //récupération des utilisateurs les + actifs (articles, sessions puis commentaires)
    public function getUsersStats(){
        return $this->manager->createQuery(
            'SELECT u.nickname,
            (SELECT COUNT(c) FROM App\Entity\Comment c WHERE c.author = u) as comments,
            (SELECT COUNT(a) FROM App\Entity\Article a WHERE a.author = u) as articles,
            (SELECT COUNT(s) FROM App\Entity\Session s WHERE u MEMBER OF s.users) as sess
            FROM App\Entity\User u
            ORDER BY articles DESC, sess DESC
            ')
            ->setMaxResults(5)
            ->getResult();
    }

    //récupération des utilisateurs les + signalés (commentaires et articles)
    public function getWorstUsers(){
        return $this->manager->createQuery(
            'SELECT u.nickname,
            (SELECT COUNT(c) FROM App\Entity\AlertComment ac JOIN ac.comment c WHERE c.author = u) as comments,
            (SELECT COUNT(a) FROM App\Entity\Alert a JOIN a.article ar WHERE ar.author = u) as articles
            FROM App\Entity\User u
            ORDER BY comments DESC, articles DESC
            ')
            ->setMaxResults(5)
            ->getResult();
    }

}
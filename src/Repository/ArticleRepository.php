<?php

namespace App\Repository;

use App\Entity\Article;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Article>
 *
 * @method Article|null find($id, $lockMode = null, $lockVersion = null)
 * @method Article|null findOneBy(array $criteria, array $orderBy = null)
 * @method Article[]    findAll()
 * @method Article[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ArticleRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Article::class);
    }

    public function add(Article $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Article $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    // les 4 derniers articles publiés ( pour la page d'accueil ) qui ne sont pas l'article à la une (adminNews)
    public function findLastArticles($adminNews, $active):array
    {
        return $this->createQueryBuilder('a')
           ->andWhere('a.adminNews = :adminNews')
           ->setParameter('adminNews', $adminNews)
           ->andWhere('a.active = :active')
           ->setParameter('active', $active)
           ->orderBy('a.createdAt', 'DESC')
           ->setMaxResults(4)
           ->getQuery()
           ->getResult()
        ;
    }

    // Les 8 derniers articles du même auteur excluant l'article actuel (suggestions du même auteur)
    public function findOtherArticlesByAuthor($id, $article, $active):array
    {
        return $this->createQueryBuilder('a')
        ->andWhere('a.author = :id')
        ->setParameter('id', $id)
        ->andWhere('a.id != :article')
        ->setParameter('article', $article)
        ->andWhere('a.active = :active')
        ->setParameter('active', $active)
        ->orderBy('a.createdAt', 'DESC')
        ->setMaxResults(8)
        ->getQuery()
        ->getResult();
    }

    // Le dernier article qu'à posté l'admin en cochant l'option adminNews (qui permet de mettre cet article "à la Une" de la page d'accueil)
    public function findAdminNewsArticle($adminNews):array
    {
        return $this->createQueryBuilder('a')
        ->andWhere('a.adminNews = :adminNews')
        ->setParameter('adminNews', $adminNews)
        ->orderBy('a.createdAt', 'DESC')
        ->setMaxResults(8)
        ->getQuery()
        ->getResult();
    }

    // Les 3 dernières questions de l'utilisateur connecté (raccourcis dans le dashboard pour avoir les commentaires de réponse de manière plus accessible)
    public function findMylastQuestions($user, $category, $active)
    {
        return $this->createQueryBuilder('a')
        ->join('a.category', 'c')
        ->andWhere('a.author = :user')
        ->setParameter('user', $user)
        ->andWhere('c.name = :category')
        ->setParameter('category', $category)
        ->andWhere('a.active = :active')
        ->setParameter('active', $active)
        ->orderBy('a.createdAt', 'DESC')
        ->setMaxResults(3)
        ->getQuery()
        ->getResult();
    }

    // compte les nombre d'article par auteur
    public function countMyArticles($user):int
    {
        return $this->createQueryBuilder('a')
        ->select('count(a)')
        ->andWhere('a.author = :user')
        ->setParameter('user', $user)
        ->orderBy('a.createdAt', 'DESC')
        ->getQuery()
        ->getSingleScalarResult();
    }

    
    // retourne les resultats qui contiennent n'importe où le mot tapé ($q). Ex: cine => FPV cinematique trouvé
    // recherche des article par titre, seulement si les articles et leurs auteurs sont actifs
    public function findByTitle($q, $active):array
    {
        $qb = $this->createQueryBuilder('a');
        $qb->leftJoin('a.author', 'u')
        ->where('a.title like :q')
        ->setParameter('q', '%'.$q.'%')
        ->andWhere('a.active = :active')
        ->andWhere('u.active = :active')
        ->setParameter('active', $active)
        ;
        return $qb->getQuery()->getResult();
    }

    // retourne les articles qui sont signalés au moins une fois
    public function findAlertedArticles():array
    {
        return $this->createQueryBuilder('a')
        ->join('a.alerts', 'al')
        ->where('al.article = a.id')
        ->orderBy('a.createdAt', 'DESC')
        ->getQuery()
        ->getResult();
    }

    //For stats service : get publications which are the most liked
    public function getMostLikedArticles(){

        $manager = $this->getEntityManager();
        return $manager->createQuery(
            'SELECT a.title, a.slug , u.nickname , a.views,
        (SELECT COUNT(l) FROM App\Entity\Likes l WHERE l.article = a ) as likes
        FROM App\Entity\Article a
        JOIN a.author u
        GROUP BY a
        ORDER BY a.views DESC')
        ->setMaxResults(5)
        ->getResult();

    }

    //For stats service : get article count
    public function getArticleCount(){
        $manager = $this->getEntityManager();
        return $manager->createQuery(
            'SELECT COUNT(a) FROM App\Entity\Article a')
            ->getSingleScalarResult();
    }

}

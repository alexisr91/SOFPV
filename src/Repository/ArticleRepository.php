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
    public function findLastArticles(){
        return $this->createQueryBuilder('a')
           ->andWhere('a.adminNews = false')
           ->andWhere('a.active = true')
           ->orderBy('a.createdAt', 'DESC')
           ->setMaxResults(4)
           ->getQuery()
           ->getResult()
       ;
    }
  

    //Les 10 derniers articles du même auteur excluant l'article actuel (suggestions)
    public function findOtherArticlesByAuthor($id, $article){
        return $this->createQueryBuilder('a')
        ->andWhere('a.author = :id')
        ->setParameter('id', $id)
        ->andWhere('a.id != :article')
        ->setParameter('article', $article->getId())
        ->andWhere('a.active = true')
        ->orderBy('a.createdAt', 'DESC')
        ->setMaxResults(8)
        ->getQuery()
        ->getResult();
    }

    //Le dernier article qu'à posté l'admin en cochant l'option adminNews (qui permet de mettre cet article "à la Une" de la page d'accueil)
    public function findAdminNewsArticle(){
        return $this->createQueryBuilder('a')
        ->andWhere('a.adminNews = true')
        ->orderBy('a.createdAt', 'DESC')
        ->setMaxResults(8)
        ->getQuery()
        ->getResult();
    }

    //Les 3 dernières questions de l'user connecté (raccourcis dans le dashboard pour avoir les réponses d'aide de manière plus accessible)
    public function findMylastQuestions($user){
        return $this->createQueryBuilder('a')
        ->join('a.category' , 'c')
        ->andWhere('a.author = :user')
        ->setParameter('user', $user)
        ->andWhere('c.name = :question')
        ->setParameter('question', 'Question')
        ->andWhere('a.active = true')
        ->orderBy('a.createdAt', 'DESC')
        ->setMaxResults(3)
        ->getQuery()
        ->getResult();
    }

    // compte les nombre d'article par auteur

    public function countMyArticles($user){
        return $this->createQueryBuilder('a')
        ->select('count(a)')
        ->andWhere('a.author = :user')
        ->setParameter('user', $user)
        ->orderBy('a.createdAt', 'DESC')
        ->getQuery()
        ->getSingleScalarResult();
    }

    //recherche des articles par critère
    public function searchByCriteria($q){
        $qb = $this->createQueryBuilder('a');
        $qb
        ->addSelect('c')
        ->addSelect('u')
        ->leftJoin('a.category', 'c')
        ->leftJoin('a.author', 'u')
        ->where( 
            $qb->expr()->orX(
                        $qb->expr()->like('a.title', ':q'),
                        $qb->expr()->like('u.nickname', ':q'), 
                        $qb->expr()->like('c.name', ':q')
            ))
        ->setParameter('q', '%'.$q.'%') //retourne les resultats qui contiennent n'importe où le mot tapé ($q). Ex: cine => FPV cinematique trouvé
        ->andWhere('a.active = true');

            return $qb->getQuery()->getResult();
    }

    //recherche des article par titre
    public function findByTitle($q){
        $qb = $this->createQueryBuilder('a');
        $qb->leftJoin('a.category', 'c')
        ->leftJoin('a.author', 'u')
        ->where('a.title like :q')
        ->setParameter('q', '%'.$q.'%')
        ->andWhere('a.active = true')
        ;

        return $qb->getQuery()->getResult();
    }


    
//    /**
//     * @return Article[] Returns an array of Article objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('a')
//            ->andWhere('a.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('a.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?Article
//    {
//        return $this->createQueryBuilder('a')
//            ->andWhere('a.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}

<?php

namespace App\Repository;

use App\Entity\User;
use App\Entity\Likes;
use App\Entity\Article;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;

/**
 * @extends ServiceEntityRepository<Likes>
 *
 * @method Likes|null find($id, $lockMode = null, $lockVersion = null)
 * @method Likes|null findOneBy(array $criteria, array $orderBy = null)
 * @method Likes[]    findAll()
 * @method Likes[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class LikesRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Likes::class);
    }

    public function add(Likes $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Likes $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    //On vérifie si il y a une corrélation entre le like, l'article et l'user pour vérifier si l'article a déjà été "liké"
    public function getLikeByUserAndArticle(User $user, Article $article)
    {
        return $this->createQueryBuilder('l')
        ->leftJoin('l.user', 'u')
        ->leftJoin('l.article', 'a')
        ->andWhere('u.id = :user')
        ->setParameter('user', $user->getId())
        ->andWhere('a.id = :article')
        ->setParameter('article', $article->getId())
        ->getQuery()
        ->getOneOrNullResult();
    }

//    /**
//     * @return Likes[] Returns an array of Likes objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('l')
//            ->andWhere('l.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('l.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?Likes
//    {
//        return $this->createQueryBuilder('l')
//            ->andWhere('l.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}

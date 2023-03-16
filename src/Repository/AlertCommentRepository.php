<?php

namespace App\Repository;

use App\Entity\User;
use App\Entity\AlertComment;
use App\Entity\Comment;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;

/**
 * @extends ServiceEntityRepository<AlertComment>
 *
 * @method AlertComment|null find($id, $lockMode = null, $lockVersion = null)
 * @method AlertComment|null findOneBy(array $criteria, array $orderBy = null)
 * @method AlertComment[]    findAll()
 * @method AlertComment[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class AlertCommentRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, AlertComment::class);
    }

    public function add(AlertComment $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(AlertComment $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    //On vérifie si il y a une corrélation entre le signalement, le commentaire et l'user pour vérifier si le commentaire a déjà été signalé
    public function getAlertByUserAndComment(User $user, Comment $comment)
    {
        return $this->createQueryBuilder('a')
        ->leftJoin('a.user', 'u')
        ->leftJoin('a.comment', 'c')
        ->andWhere('u.id = :user')
        ->setParameter('user', $user->getId())
        ->andWhere('c.id = :comment')
        ->setParameter('comment', $comment->getId())
        ->getQuery()
        ->getOneOrNullResult();
    }

//    /**
//     * @return AlertComment[] Returns an array of AlertComment objects
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

//    public function findOneBySomeField($value): ?AlertComment
//    {
//        return $this->createQueryBuilder('a')
//            ->andWhere('a.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
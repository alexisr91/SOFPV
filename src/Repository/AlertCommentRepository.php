<?php

namespace App\Repository;

use App\Entity\AlertComment;
use App\Entity\Comment;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

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

    // On vérifie si il y a une corrélation entre le signalement, le commentaire et l'user pour vérifier si le commentaire a déjà été signalé
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

}

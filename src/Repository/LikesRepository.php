<?php

namespace App\Repository;

use App\Entity\Article;
use App\Entity\Likes;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

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

    // On vérifie si il y a une corrélation entre le like, l'article et l'user pour vérifier si l'article a déjà été "liké"
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

}

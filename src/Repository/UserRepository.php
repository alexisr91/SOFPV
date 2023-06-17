<?php

namespace App\Repository;

use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\PasswordUpgraderInterface;

/**
 * @extends ServiceEntityRepository<User>
 *
 * @method User|null find($id, $lockMode = null, $lockVersion = null)
 * @method User|null findOneBy(array $criteria, array $orderBy = null)
 * @method User[]    findAll()
 * @method User[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UserRepository extends ServiceEntityRepository implements PasswordUpgraderInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, User::class);
    }

    public function add(User $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(User $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    /**
     * Used to upgrade (rehash) the user's password automatically over time.
     */
    public function upgradePassword(PasswordAuthenticatedUserInterface $user, string $newHashedPassword): void
    {
        if (!$user instanceof User) {
            throw new UnsupportedUserException(sprintf('Instances of "%s" are not supported.', \get_class($user)));
        }

        $user->setPassword($newHashedPassword);

        $this->add($user, true);
    }

    // search user by nickname - only active users
    // recherche de de l'user par pseudo, résultats seulement pour les utilisateurs actifs
    public function findByPseudo($q, $active):array
    {
        $qb = $this->createQueryBuilder('u');
        $qb->leftJoin('u.articles', 'a')
        ->where('u.nickname like :q')
        ->setParameter('q', '%'.$q.'%')
        ->andWhere('u.active = :active')
        ->setParameter('active', $active)
        ;

        return $qb->getQuery()->getResult();
    }

    // get list of users subscribed to a session
    // récupère la liste des utilisateurs inscrits à la session
    public function findIfAlreadyRegisteredOnSession($session):array
    {
        return $this->createQueryBuilder('u')
                    ->innerJoin('u.sessions', 's')
                    ->andWhere('s.id = :session')
                    ->setParameter('session', $session)
                    ->getQuery()
                    ->getResult()
        ;
    }

    //For Stats service : get user count
    public function getUsersCount() :int
    {   $manager = $this->getEntityManager();
        return $manager->createQuery(
            'SELECT COUNT(u) FROM App\Entity\User u')
            ->getSingleScalarResult();
    }

    //for stats service : get users Stats (most actives users : sessions, publications, comments)
    public function getUsersStats():array
    {
        $manager = $this->getEntityManager();
        return $manager->createQuery(
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

    //for stats service : worst users (most reported with them publications and comments)
    public function getWorstUsers(): array
    {
        $manager = $this->getEntityManager();
        return $manager->createQuery(
            'SELECT u.nickname,
            (SELECT COUNT(c) FROM App\Entity\AlertComment ac JOIN ac.comment c WHERE c.author = u) as comments,
            (SELECT COUNT(a) FROM App\Entity\Alert a JOIN a.article ar WHERE ar.author = u) as articles
            FROM App\Entity\User u
            ORDER BY comments DESC, articles DESC
            ')
            ->setMaxResults(5)
            ->getResult();
    }


    //    /**
    //     * @return User[] Returns an array of User objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('u')
    //            ->andWhere('u.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('u.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?User
    //    {
    //        return $this->createQueryBuilder('u')
    //            ->andWhere('u.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}

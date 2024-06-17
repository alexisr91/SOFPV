<?php

namespace App\Repository;

use App\Entity\Order;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Order>
 *
 * @method Order|null find($id, $lockMode = null, $lockVersion = null)
 * @method Order|null findOneBy(array $criteria, array $orderBy = null)
 * @method Order[]    findAll()
 * @method Order[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class OrderRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Order::class);
    }

    public function add(Order $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Order $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    // commandes finalisées et payées par user
    public function findOrderSucceededByUser($user, $status): array
    {
        return $this->createQueryBuilder('o')
            ->andWhere('o.user = :user')
            ->setParameter('user', $user)
            ->andWhere('o.status_stripe = :status')
            ->setParameter('status', $status)
            ->orderBy('o.createdAt', 'DESC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }

    // trouve le status de la dernière commande de l'user (mon profil)
    public function findLastOrder($user, $status)
    {
        return $this->createQueryBuilder('o')
        ->join('o.delivery_status', 's')
        ->where('o.user = :user')
        ->setParameter('user', $user)
        ->andWhere('s.status != :status')
        ->setParameter('status', $status)
        ->orderBy('o.createdAt', 'DESC')
        ->setMaxResults(1)
        ->getQuery()
        ->getOneOrNullResult()
        ;
    }

    // admin: commandes à traiter (les plus anciennes en premier)
    public function findOrderToMake($status)
    {
        return $this->createQueryBuilder('o')
        ->join('o.delivery_status', 's')
        ->where('s.status = :status')
        ->setParameter('status', $status)
        ->orderBy('o.createdAt', 'ASC')
        ->getQuery()
        ->getResult()
        ;
    }

    //for stat service : get orders count
    public function getOrderCount():int
    {
        $manager = $this->getEntityManager();
        return $manager->createQuery(
            'SELECT COUNT(o) FROM App\Entity\Order o')
            ->getSingleScalarResult();
    }

}

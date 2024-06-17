<?php

namespace App\Repository;

use App\Entity\Drone;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Drone>
 *
 * @method Drone|null find($id, $lockMode = null, $lockVersion = null)
 * @method Drone|null findOneBy(array $criteria, array $orderBy = null)
 * @method Drone[]    findAll()
 * @method Drone[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class DroneRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Drone::class);
    }

    public function add(Drone $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Drone $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

}

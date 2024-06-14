<?php

namespace App\Repository;

use App\Entity\MapSpot;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<MapSpot>
 *
 * @method MapSpot|null find($id, $lockMode = null, $lockVersion = null)
 * @method MapSpot|null findOneBy(array $criteria, array $orderBy = null)
 * @method MapSpot[]    findAll()
 * @method MapSpot[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class MapSpotRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, MapSpot::class);
    }

    public function add(MapSpot $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(MapSpot $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

}

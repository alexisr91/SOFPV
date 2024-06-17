<?php

namespace App\Repository;

use App\Entity\AdminResponseContact;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<AdminResponseContact>
 *
 * @method AdminResponseContact|null find($id, $lockMode = null, $lockVersion = null)
 * @method AdminResponseContact|null findOneBy(array $criteria, array $orderBy = null)
 * @method AdminResponseContact[]    findAll()
 * @method AdminResponseContact[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class AdminResponseContactRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, AdminResponseContact::class);
    }

    public function add(AdminResponseContact $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(AdminResponseContact $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

}

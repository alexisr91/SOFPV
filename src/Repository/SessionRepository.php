<?php

namespace App\Repository;

use App\Entity\Session;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Session>
 *
 * @method Session|null find($id, $lockMode = null, $lockVersion = null)
 * @method Session|null findOneBy(array $criteria, array $orderBy = null)
 * @method Session[]    findAll()
 * @method Session[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class SessionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Session::class);
    }

    public function add(Session $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Session $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    // verifie la corrélation entre une session dejà existante pour éviter les doublons
    //check if a session exists with same id, date and timesheet
    public function isSessionAlreadyExist($id, $date, $timeSheet)
    {
        return $this->createQueryBuilder('s')
        ->join('s.mapSpot', 'm')
        ->where('m.id = :id')
        ->setParameter('id', $id)
        ->andWhere('s.date = :date')
        ->setParameter('date', $date)
        ->andWhere('s.timesheet = :timesheet')
        ->setParameter('timesheet', $timeSheet)
        ->getQuery()
        ->getOneOrNullResult()
        ;
    }

    // retourne les 5 dernieres sessions ajoutées
    public function findLastSessions()
    {
        return $this->createQueryBuilder('s')
        ->where('s.past = false')
        ->orderBy('s.date', 'ASC')
        ->setMaxResults(6)
        ->getQuery()
        ->getResult()
        ;
    }

    // retourne les 4 prochaines sessions de l'user concerné (profil public)
    public function findSessionsForUser($user)
    {
        return $this->createQueryBuilder('s')
        ->join('s.users', 'u')
        ->where('s.past = false')
        ->andWhere('u = :user')
        ->setParameter('user', $user)
        ->orderBy('s.date', 'ASC')
        ->setMaxResults(4)
        ->getQuery()
        ->getResult();
    }

    // retourne toutes les sessions de l'user concerné (profil public)
    public function findAllSessionsForUser($user, $sessionStatus)
    {
        return $this->createQueryBuilder('s')
        ->join('s.users', 'u')
        ->where('s.past = :sessionStatus')
        ->setParameter('sessionStatus', $sessionStatus)
        ->andWhere('u = :user')
        ->setParameter('user', $user)
        ->orderBy('s.date', 'ASC')
        ->getQuery()
        ->getResult();
    }

    // compte le nombre de sessions actives de l'user (dashboard)
    public function countMySessions($user, $sessionStatus):int
    {
        return $this->createQueryBuilder('s')
        ->join('s.users', 'u')
        ->select('count(s)')
        ->where('u = :user')
        ->setParameter('user', $user)
        ->andWhere('s.past = :sessionStatus')
        ->setParameter('sessionStatus', $sessionStatus)
        ->getQuery()
        ->getSingleScalarResult();
    }

    //for stats service : count sessions
    public function getSessionsCount():int
    {
        $manager = $this->getEntityManager();
        return $manager->createQuery(
            'SELECT COUNT(s) FROM App\Entity\Session s')
            ->getSingleScalarResult();
    }

}

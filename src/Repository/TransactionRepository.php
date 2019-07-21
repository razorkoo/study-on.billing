<?php

namespace App\Repository;

use App\Entity\Transaction;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method Transaction|null find($id, $lockMode = null, $lockVersion = null)
 * @method Transaction|null findOneBy(array $criteria, array $orderBy = null)
 * @method Transaction[]    findAll()
 * @method Transaction[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class TransactionRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, Transaction::class);
    }
    public function findWithFilters($user, $type, $skipExpired, $courseId)
    {
        $queryBilder = $this->createQueryBuilder('t');
        if ($user) {
            $queryBilder->andWhere('t.bUser = :bUser')
                ->setParameter('bUser', $user);
        }
        if ($type) {
            $queryBilder->andWhere('t.type = :type')
                ->setParameter('type', $type);
        }
        if ($skipExpired == 1) {
            $date = new \DateTime();
            $queryBilder->andWhere('t.expiredat > :date')
                ->setParameter('date', $date);
        }
        if ($courseId) {
            $queryBilder->andWhere('t.course = :courseCode')
                ->setParameter('courseCode', $courseId);
        }
        return $queryBilder->getQuery()->getResult();
    }
    public function findByDate(\DateTime $start, \DateTime $end, $type = null, $user = null, $report = null, $course = null)
    {
        $queryBilder = $this->createQueryBuilder('t');
        if (isset($type)) {
            $queryBilder->andWhere('t.type = :type')
                ->setParameter('type', $type);
        }
        if (isset($user)) {
            $queryBilder->andWhere('t.bUser = :user')
                ->setParameter('user',$user);
        }
        if (!isset($report)) {
            $queryBilder->andWhere('t.expiredat between :start and :end')
                ->setParameter('start', $start)
                ->setParameter('end', $end);
        } else {
            $queryBilder->andWhere('t.createdat between :start and :end')
                ->setParameter('start', $start)
                ->setParameter('end', $end);
        }
        if (isset($course)) {
            $queryBilder->andWhere('t.course = :course')
                ->setParameter('course',$course);
        }
        return $queryBilder->getQuery()->getResult();
    }

}

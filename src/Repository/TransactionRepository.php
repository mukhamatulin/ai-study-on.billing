<?php

namespace App\Repository;

use App\Entity\Transaction;
use App\Entity\User;
use DateTimeImmutable;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Transaction>
 */
class TransactionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Transaction::class);
    }

    /**
     * @return Transaction[]
     */
    public function findForUser(User $user, array $filters): array
    {
        $qb = $this->createQueryBuilder('t')
            ->leftJoin('t.course', 'c')
            ->addSelect('c')
            ->andWhere('t.user = :user')
            ->setParameter('user', $user)
            ->addOrderBy('t.createdAt', 'DESC');

        if (!empty($filters['type'])) {
            $qb->andWhere('t.type = :type')->setParameter('type', $filters['type']);
        }

        if (!empty($filters['course_code'])) {
            $qb->andWhere('c.code = :courseCode')->setParameter('courseCode', $filters['course_code']);
        }

        if (!empty($filters['skip_expired'])) {
            $qb->andWhere('t.expiresAt IS NULL OR t.expiresAt > :now')->setParameter('now', new DateTimeImmutable());
        }

        return $qb->getQuery()->getResult();
    }

    /**
     * @return Transaction[]
     */
    public function findEndingRentals(DateTimeImmutable $from, DateTimeImmutable $to): array
    {
        return $this->createQueryBuilder('t')
            ->join('t.course', 'c')
            ->join('t.user', 'u')
            ->addSelect('c', 'u')
            ->andWhere('t.type = :type')
            ->andWhere('t.expiresAt >= :from')
            ->andWhere('t.expiresAt < :to')
            ->setParameter('type', Transaction::TYPE_PAYMENT)
            ->setParameter('from', $from)
            ->setParameter('to', $to)
            ->getQuery()
            ->getResult();
    }

    public function getPaymentReport(DateTimeImmutable $from, DateTimeImmutable $to): array
    {
        return $this->createQueryBuilder('t')
            ->select('c.title title, c.type course_type, COUNT(t.id) payment_count, SUM(t.amount) total_amount')
            ->join('t.course', 'c')
            ->andWhere('t.type = :type')
            ->andWhere('t.createdAt >= :from')
            ->andWhere('t.createdAt < :to')
            ->groupBy('c.id')
            ->addOrderBy('c.title', 'ASC')
            ->setParameter('type', Transaction::TYPE_PAYMENT)
            ->setParameter('from', $from)
            ->setParameter('to', $to)
            ->getQuery()
            ->getArrayResult();
    }
}

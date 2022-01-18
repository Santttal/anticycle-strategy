<?php

namespace App\Repository;

use App\Entity\InstrumentHistory;
use Carbon\Carbon;
use DateTimeInterface;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method find(string $id): AntiTrendInstrument
 */
class InstrumentHistoryRepository extends AbstractRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, InstrumentHistory::class);
    }

    public function findLast(?string $name): ?InstrumentHistory
    {
        $qb = $this->createQueryBuilder('sp');

        $qb
            ->orderBy('sp.date', 'DESC')
            ->setMaxResults(1)
        ;

        if (null !== $name) {
            $qb
                ->where('sp.name = :name')
                ->setParameter('name', $name)
            ;
        }

        return $qb->getQuery()->getOneOrNullResult();
    }

    public function findByDate(string $name, ?DateTimeInterface $date): ?InstrumentHistory
    {
        $qb = $this->createQueryBuilder('sp');

        $qb
            ->where('sp.name = :name')
            ->andWhere('sp.date = :date')
            ->setParameter('name', $name)
            ->setParameter('date', $date)
        ;

        return $qb->getQuery()->getOneOrNullResult();
    }

    /**
     * @return InstrumentHistory[]
     */
    public function findAllInInterval(string $name, DateTimeInterface $startAt, DateTimeInterface $endAt): array
    {
        $qb = $this->createQueryBuilder('sp');

        $qb
            ->where('sp.name = :name')
            ->andWhere('sp.date >= :start_date')
            ->andWhere('sp.date <= :end_date')
            ->setParameter('name', $name)
            ->setParameter('start_date', $startAt)
            ->setParameter('end_date', $endAt)
        ;

        return $qb->getQuery()->getResult();
    }

    public function calculateAvg(string $instrumentName, DateTimeInterface $date, string $interval): float
    {
        $startAt = Carbon::make($date);
        $endAt = clone $startAt;
        $startAt->modify($interval);

        $qb = $this->getEntityManager()->createQueryBuilder();
        $qb->select('AVG(sp.value) as value')
            ->from(InstrumentHistory::class, 'sp')
            ->andWhere('sp.date > :start_at')
            ->andWhere('sp.date < :end_at')
            ->andWhere('sp.name = :name')
            ->setParameter('start_at', $startAt)
            ->setParameter('end_at', $endAt)
            ->setParameter('name', $instrumentName)
            ;

        return $qb->getQuery()->getSingleScalarResult();
    }
}

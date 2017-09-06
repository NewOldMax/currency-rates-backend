<?php

namespace CurrencyRates\Service\CurrencyRate;

use CurrencyRates\Entity\User;
use Doctrine\ORM\EntityRepository;

class CurrencyRateRepository extends EntityRepository
{
    public function findFromDate(
        \DateTime $date,
        string $currency
    ) {
        return $this->createQueryBuilder('r')
            ->select('r')
            ->where('r.date >= :date')
            ->andWhere('r.currency = :currency')
            ->setParameter('date', $date)
            ->setParameter('currency', $currency)
            ->orderBy('r.date', 'DESC')
            ->getQuery()->getResult();
    }
}

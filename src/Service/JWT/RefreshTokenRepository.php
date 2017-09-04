<?php

namespace CurrencyRates\Service\JWT;

use Doctrine\ORM\EntityRepository;

class RefreshTokenRepository extends EntityRepository
{
    public function getExpired(\DateTime $date)
    {
        return $this->createQueryBuilder('t')
            ->select('t')
            ->where('t.valid <= :date')
            ->setParameter('date', $date)
            ->getQuery()->getResult();
    }
}

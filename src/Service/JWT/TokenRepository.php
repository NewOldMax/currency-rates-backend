<?php

namespace CurrencyRates\Service\JWT;

use Doctrine\ORM\EntityRepository;

class TokenRepository extends EntityRepository
{
    public function getExpired(\DateTime $date)
    {
        return $this->createQueryBuilder('t')
            ->select('t')
            ->where('t.expiredAt <= :date')
            ->setParameter('date', $date)
            ->getQuery()->getResult();
    }
}

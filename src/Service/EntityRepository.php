<?php

namespace SalesGuru\Service;

use Doctrine\ORM\EntityRepository as BaseEntityRepository;

class EntityRepository extends BaseEntityRepository
{
    /**
     * @param BasicEntity $entity
     */
    public function save(BasicEntity $entity)
    {
        $this->getEntityManager()->persist($entity);
    }

    /**
     * @param mixed $object
     */
    public function flush($object)
    {
        $this->getEntityManager()->flush($object);
    }
}

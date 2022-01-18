<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Entity;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;

abstract class AbstractRepository extends ServiceEntityRepository
{
    public function add(Entity $entity): void
    {
        $this->getEntityManager()->persist($entity);
    }

    public function remove(Entity $entity): void
    {
        $this->getEntityManager()->remove($entity);
    }

    public function save(): void
    {
        $this->getEntityManager()->flush();
    }

    public function deleteAll(): void
    {
        $tableName = $this->getClassMetadata()->getTableName();
        $query = sprintf('DELETE FROM %s', $tableName);
        $this->getEntityManager()->getConnection()->executeQuery($query);
    }
}

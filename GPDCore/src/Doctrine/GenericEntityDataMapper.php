<?php

namespace GPDCore\Doctrine;

use Doctrine\ORM\EntityManager;
use GPDCore\Contracts\EntityDataMapperInterface;

final class GenericEntityDataMapper implements EntityDataMapperInterface
{


    private EntityManager $entityManager;

    public function __construct(EntityManager $entityManager)
    {
        $this->entityManager = $entityManager;
    }
    public function createEntity(string $entityClass, array $data): object
    {
        $entity = new $entityClass();
        $entity = EntityHydrator::hydrate($this->entityManager, $entity, $data);
        return $entity;
    }
    public function updateEntity(object $entity, array $data): object
    {
        $entity = EntityHydrator::hydrate($this->entityManager, $entity, $data);
        return $entity;
    }
}

<?php

declare(strict_types=1);

namespace GPDCore\Doctrine;

use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\EntityManager;
use ReflectionClass;
use ReflectionMethod;

/**
 * Hydrator para poblar entidades Doctrine con datos de arrays.
 * 
 * Mapea automáticamente propiedades de arrays a métodos setter de entidades,
 * incluyendo manejo especial para asociaciones de colecciones.
 */
class EntityHydrator
{
    /**
     * Hidrata una entidad con valores de un array.
     * 
     * Solo asigna valores que coinciden con métodos setter públicos de la entidad.
     * Maneja automáticamente asociaciones de colecciones usando EntityManager.
     *
     * @param EntityManager $entityManager Gestor de entidades de Doctrine
     * @param object $entity Entidad a hidratar
     * @param array<string, mixed> $data Datos a asignar (key => value)
     * @return object La entidad hidratada
     */
    public static function hydrate(EntityManager $entityManager, object $entity, array $data): object
    {
        $reflectionClass = new ReflectionClass($entity);
        $collectionAssociations = EntityUtilities::getCollections($entityManager, get_class($entity));

        foreach ($data as $propertyName => $value) {
            $collectionAssociation = $collectionAssociations[$propertyName] ?? null;

            if ($collectionAssociation !== null) {
                self::hydrateCollection($entityManager, $entity, $collectionAssociation, $value);
                continue;
            }

            $methodName = 'set' . ucfirst($propertyName);
            $method = self::getMethod($reflectionClass, $methodName);
            self::invokeMethod($entity, $method, $value);
        }

        return $entity;
    }

    /**
     * Obtiene un método público de una clase mediante reflection.
     *
     * @param ReflectionClass $reflectionClass Clase de la que obtener el método
     * @param string $methodName Nombre del método
     * @return ReflectionMethod|null El método si existe y es público, null en caso contrario
     */
    protected static function getMethod(ReflectionClass $reflectionClass, string $methodName): ?ReflectionMethod
    {
        if (!$reflectionClass->hasMethod($methodName)) {
            return null;
        }

        $method = $reflectionClass->getMethod($methodName);

        if (!($method->getModifiers() & ReflectionMethod::IS_PUBLIC)) {
            return null;
        }

        return $method;
    }

    /**
     * Invoca un método en una entidad con un valor dado.
     *
     * @param object $entity Entidad sobre la que invocar el método
     * @param ReflectionMethod|null $method Método a invocar
     * @param mixed $value Valor a pasar como argumento
     */
    protected static function invokeMethod(object $entity, ?ReflectionMethod $method, mixed $value): void
    {
        if ($method === null) {
            return;
        }

        $method->invoke($entity, $value);
    }

    /**
     * Hidrata una asociación de colección en una entidad.
     * 
     * Limpia la colección existente y la sincroniza con las entidades
     * correspondientes a los IDs proporcionados.
     *
     * @param EntityManager $entityManager Gestor de entidades de Doctrine
     * @param object $entity Entidad que contiene la colección
     * @param EntityAssociation $relation Metadata de la asociación
     * @param mixed $value Array de IDs o null/vacío para limpiar la colección
     */
    protected static function hydrateCollection(
        EntityManager $entityManager,
        object $entity,
        EntityAssociation $relation,
        mixed $value
    ): void {
        $property = $relation->getFieldName();
        $reflectionClass = new ReflectionClass($entity);
        $methodName = 'get' . ucfirst($property);
        $method = self::getMethod($reflectionClass, $methodName);

        if ($method === null) {
            return;
        }

        /** @var Collection $collection */
        $collection = $method->invoke($entity);
        $collection->clear();

        // Si el valor está vacío solo elimina todas las relaciones
        if (empty($value) || !is_array($value)) {
            return;
        }

        $identifier = $relation->getIdentifier();
        $qb = $entityManager->createQueryBuilder()
            ->from($relation->getTargetEntity(), 'entity')
            ->select('entity');

        $qb->andWhere($qb->expr()->in("entity.{$identifier}", ':ids'))
            ->setParameter(':ids', $value);

        $result = $qb->getQuery()->getResult();

        foreach ($result as $item) {
            $collection->add($item);
        }
    }
}

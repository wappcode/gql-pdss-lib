<?php

declare(strict_types=1);

namespace GPDCore\Doctrine;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Mapping\ManyToManyAssociationMapping;
use Doctrine\ORM\Mapping\OneToManyAssociationMapping;
use ReflectionClass;
use ReflectionMethod;
use RuntimeException;

/**
 * Helper para trabajar con metadata de entidades Doctrine.
 *
 * Proporciona métodos para obtener información sobre identificadores,
 * asociaciones y metadatos de entidades.
 */
class EntityMetadataHelper
{
    /**
     * Obtiene el nombre del campo identificador (clave primaria) de una entidad.
     *
     * @param EntityManager $entityManager Gestor de entidades
     * @param string        $className     Nombre completo de la clase de la entidad
     *
     * @return string Nombre del campo identificador
     *
     * @throws RuntimeException Si la entidad no tiene identificador
     */
    public static function getIdFieldName(EntityManager $entityManager, string $className): string
    {
        $metadata = $entityManager->getClassMetadata($className);

        if (empty($metadata->identifier)) {
            throw new RuntimeException("Entity {$className} has no identifier defined");
        }

        return $metadata->identifier[0];
    }

    /**
     * Extrae el valor del identificador de una entidad.
     *
     * @param EntityManager $entityManager Gestor de entidades
     * @param object        $entity        Instancia de la entidad
     *
     * @return mixed Valor del identificador o null si no se puede obtener
     */
    public static function extractEntityId(EntityManager $entityManager, object $entity): mixed
    {
        $className = get_class($entity);
        $identifier = self::getIdFieldName($entityManager, $className);
        $reflectionClass = new ReflectionClass($entity);
        $methodName = 'get' . ucfirst($identifier);

        if (!$reflectionClass->hasMethod($methodName)) {
            return null;
        }

        $method = $reflectionClass->getMethod($methodName);

        if (!($method->getModifiers() & ReflectionMethod::IS_PUBLIC)) {
            return null;
        }

        return $method->invoke($entity);
    }

    /**
     * Obtiene las asociaciones con columnas de join de la entidad.
     *
     * Retorna solo asociaciones con una única columna de join (ManyToOne, OneToOne).
     * Las claves del array son los nombres de las propiedades relacionadas.
     *
     * @param EntityManager $entityManager Gestor de entidades
     * @param string        $className     Nombre completo de la clase de la entidad
     *
     * @return array<string, EntityAssociation> Array asociativo [propertyName => EntityAssociation]
     */
    public static function getJoinColumnAssociations(EntityManager $entityManager, string $className): array
    {
        $metadata = $entityManager->getClassMetadata($className);
        $associations = $metadata->associationMappings;

        // Filtrar solo asociaciones con una columna de join
        $associations = array_filter(
            $associations,
            fn ($association) => count($association->joinColumns ?? []) === 1
        );

        // Mapear a objetos EntityAssociation
        $associations = array_map(
            fn ($association) => self::createAssociationFromMapping($entityManager, $association),
            $associations
        );

        return $associations;
    }

    /**
     * Obtiene las asociaciones de tipo colección de la entidad.
     *
     * Retorna solo asociaciones OneToMany y ManyToMany.
     *
     * @param EntityManager $entityManager Gestor de entidades
     * @param string        $className     Nombre completo de la clase de la entidad
     *
     * @return array<string, EntityAssociation> Array asociativo [propertyName => EntityAssociation]
     */
    public static function getCollectionAssociations(EntityManager $entityManager, string $className): array
    {
        $metadata = $entityManager->getClassMetadata($className);
        $associations = $metadata->associationMappings;

        // Filtrar solo asociaciones de tipo colección
        $associations = array_filter(
            $associations,
            fn ($association) => $association instanceof OneToManyAssociationMapping
                || $association instanceof ManyToManyAssociationMapping
        );

        // Mapear a objetos EntityAssociation
        $associations = array_map(
            fn ($association) => self::createAssociationFromMapping($entityManager, $association),
            $associations
        );

        return $associations;
    }

    /**
     * Crea un objeto EntityAssociation a partir de un mapping de asociación.
     *
     * @param EntityManager $entityManager      Gestor de entidades
     * @param object        $associationMapping Mapping de la asociación de Doctrine
     *
     * @return EntityAssociation Objeto con información de la asociación
     *
     * @throws RuntimeException Si la entidad objetivo no tiene identificador
     */
    protected static function createAssociationFromMapping(
        EntityManager $entityManager,
        object $associationMapping
    ): EntityAssociation {
        $fieldName = $associationMapping->fieldName;
        $targetEntity = $associationMapping->targetEntity;
        $associationMetadata = $entityManager->getClassMetadata($targetEntity);

        if (empty($associationMetadata->identifier)) {
            throw new RuntimeException("Target entity {$targetEntity} has no identifier defined");
        }

        $identifier = $associationMetadata->identifier[0];

        return new EntityAssociation($fieldName, $identifier, $targetEntity);
    }
}

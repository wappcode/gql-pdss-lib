<?php

namespace GPDCore\Library;

use ReflectionClass;
use ReflectionMethod;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Mapping\Entity;
use GPDCore\Library\EntityAssociation;

class EntityUtilities
{


    /**
     * Recupera el nombre de la propiedad que esta como clave primaria
     *
     * @param EntityManager $entityManager
     * @param string $className
     * @return string
     */
    public static function getFirstIdentifier(EntityManager $entityManager, string $className): string
    {
        $metadata = $entityManager->getClassMetadata($className);
        $identifier = $metadata->identifier[0];
        return $identifier;
    }

    public static function getFirstIdentifierValue(EntityManager $entityManager, $entity)
    {
        $className = get_class($entity);
        $identifier = static::getFirstIdentifier($entityManager, $className);
        $refl = new ReflectionClass($entity);
        $methodName = 'get' . ucfirst($identifier);
        if ($refl->hasMethod($methodName)) {
            $method = $refl->getMethod($methodName);
            if ($method->getModifiers() & ReflectionMethod::IS_PUBLIC) {
                return $method->invoke($entity);
            }
        }
        return null;
    }
    /**
     * Recupera un array con las  asociaciones de la entidad que estan registradas en una columna de la tabla de la entidad en la base de datos
     * Importante las claves del array son los nombres de las propiedades relacionadas.
     *  
     * @return array [associationName => EntityAsociation,...]
     */
    public static function getColumnAssociations(EntityManager $entityManager, string $className): array
    {
        $metadata = $entityManager->getClassMetadata($className);

        $associations = $metadata->associationMappings;
        $associations = array_filter($associations, function ($association) {
            $joinColumns = $association["joinColumns"] ?? [];
            return count($joinColumns) === 1;
        });
        $associations = array_map(function ($association) use ($entityManager) {
            return static::createAssociationValue($entityManager, $association);
        }, $associations);

        return $associations;
    }

    /**
     * Recupera un array con las propiedades de las asociaciones relacionadas con la entidad que son de tipo collection
     *
     * @param EntityManager $entityManager
     * @param string $className
     * @return array EntityAssociation[]
     */
    public static function getCollections(EntityManager $entityManager, string $className): array
    {
        $metadata = $entityManager->getClassMetadata($className);

        $associations = $metadata->associationMappings;
        $associations = array_filter($associations, function ($association) use ($className) {

            $refl = new ReflectionClass($className);
            $property = $association["fieldName"];
            $propertyDocs = $refl->getProperty($property)->getDocComment();
            return preg_match("/(OneToMany\s*\()|(ManyToMany\s*\()/", $propertyDocs);
        });
        $associations = array_map(function ($association) use ($entityManager) {
            return static::createAssociationValue($entityManager, $association);
        }, $associations);

        return $associations;
    }

    /**
     * Crea un objeto EntityAssociation
     *
     * @param EntityManager $entityManager
     * @param array $association
     * @return array EntityAssociation[]
     */
    protected static function createAssociationValue(EntityManager $entityManager, array $association): EntityAssociation
    {
        $fieldName = $association["fieldName"];
        $targetEntity = $association["targetEntity"];
        $associationMetadata = $entityManager->getClassMetadata($targetEntity);
        $identifier = $associationMetadata->identifier[0];
        $result = new EntityAssociation($fieldName, $identifier, $targetEntity);
        return $result;
    }
}

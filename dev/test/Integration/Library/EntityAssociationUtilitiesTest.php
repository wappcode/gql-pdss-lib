<?php

use AppModule\Entities\Post;
use AppModule\Entities\User;
use GPDCore\Doctrine\EntityAssociation;
use GPDCore\Doctrine\EntityUtilities;

class EntityUtilitiesTest extends PHPUnit\Framework\TestCase
{
    public function testAssociationJoinColumns()
    {
        global $entityManager;
        $associations = EntityUtilities::getColumnAssociations($entityManager, Post::class);
        $hasAssociations = count($associations) === 1;
        /** @var EntityAssociation */
        $relation = $associations['author'];
        $fieldName = $relation->getFieldName();
        $identifier = $relation->getIdentifier();
        $this->assertTrue($hasAssociations, 'La entidad Post debe tener una relación');
        $this->assertEquals('author', $fieldName, 'La entidad Post debe tener la asociación author');
        $this->assertEquals('id', $identifier, 'La entidad Post debe tener como identificador la propiedad id');
    }

    public function testAssociationCollections()
    {
        global $entityManager;
        $associations = EntityUtilities::getCollections($entityManager, User::class);
        $accountId = $associations['accounts']->getIdentifier();
        $this->assertEquals('code', $accountId, 'El identificador de una cuenta debe ser la propiedad code');
    }
}

<?php

use ReflectionClass;
use AppModule\Entities\Post;
use AppModule\Entities\User;
use AppModule\Entities\Account;
use GPDCore\Library\EntityAssociation;
use GPDCore\Library\EntityAssociationUtilities;

class EntityAssociationUtilitiesTest extends \PHPUnit\Framework\TestCase
{
	public function testAssociationJoinColumns()
	{

		global $entityManager;
		$associations = EntityAssociationUtilities::getWithJoinColumns($entityManager, Post::class);
		$hasAssociations =  count($associations) === 1;
		/** @var EntityAssociation */
		$relation = $associations["author"];
		$fieldName = $relation->getFieldName();
		$identifier = $relation->getIdentifier();
		$this->assertTrue($hasAssociations, "La entidad Post debe tener una relación");
		$this->assertEquals("author", $fieldName, "La entidad Post debe tener la asociación author");
		$this->assertEquals("id", $identifier, "La entidad Post debe tener como identificador la propiedad id");
	}

	public function testAssociationCollections()
	{
		global $entityManager;
		$associations = EntityAssociationUtilities::getCollections($entityManager, User::class);
		$accountId = $associations["accounts"]->getIdentifier();
		$this->assertEquals("code", $accountId, "El identificador de una cuenta debe ser la propiedad code");
	}
}

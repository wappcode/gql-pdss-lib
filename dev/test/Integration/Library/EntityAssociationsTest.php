<?php

use AppModule\Entities\Account;
use AppModule\Entities\Post;
use AppModule\Entities\User;
use GPDCore\Library\EntityAssociations;

class EntityAssociationsTest extends \PHPUnit\Framework\TestCase
{
	public function testInititalIntegration()
	{

		global $entityManager;
		$associations = EntityAssociations::getWithJoinColumns($entityManager, Post::class);
		$hasAssociations =  count($associations) === 1;
		$relation = $associations[0];
		$this->assertTrue($hasAssociations, "La entidad Post debe tener una relación");
		$this->assertEquals("author", $relation["fieldName"], "La entidad Post debe tener la asociación author");
		$this->assertEquals("id", $relation["identifier"], "La entidad Post debe tener la asociación author");
	}
}

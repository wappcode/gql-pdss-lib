<?php

use Doctrine\ORM\EntityManager;
use GQLBasicClient\GQLClient;

class CRUDUserTest extends \PHPUnit\Framework\TestCase
{

  /**
   *
   * @var EntityManager
   */
  private $entityManager;

  /**
   *
   * @var GQLClient
   */
  private $gqlClient;



  /**
   * This method is called before the first test of this test class is run.
   */
  protected function setUp(): void
  {
    global $entityManager;
    $this->entityManager = $entityManager;
    $this->gqlClient = new GQLClient("http://localhost:8000/api");
  }

  public function testCreateUser()
  {
    $id = $this->createUser();
    $newName = "Pancho López Actualizado";
    $updatedName = $this->updateUserName($id, $newName);
    $connection = $this->getDataUsersConnection();
    $userId = $this->getUserId($id);
    $deletedUserId = $this->deleteUser($id);
    $this->assertNotEmpty($id);
    $this->assertEquals($newName, $updatedName, "Actualizar un usuario");
    $this->assertGreaterThan(0, $connection["totalCount"], "Debe haber almenos un registro");
    $this->assertEquals(0, count($connection["edges"]), "No debe haber edges porque no se agrego información de paginación");
    $this->assertEquals($id, $userId, "Consulta que se obtengan datos al consultar un elmento con id");
    $this->assertEquals($id, $deletedUserId, "Eliminar un usuario");
  }


  private function  createUser(): string
  {
    $query = '
    mutation MutationCreateUser($input: UserInput!){
        user:createUser(input:$input) {
          id
          name
          email
          accounts {
            code
          }
        }
      }
    
    ';
    $variables = ["input" => [
      "name" => "Pancho López",
      "email" => "plopez@demo.local.lan",
      "accounts" => []
    ]];
    $result = $this->gqlClient->execute($query, $variables);
    $id = $result["data"]["user"]["id"] ?? null;
    return $id;
  }

  private function updateUserName(string $id, string $nameUpdated): string
  {
    $query = '
    mutation MutationUpdateUser($id: ID!,$input: UserPartialInput!){
        user:updateUser(id: $id, input:$input) {
          id
          name
          email
          accounts {
            code
          }
        }
      }
    
    ';

    $variables = [
      "id" => $id,
      "input" => [
        "name" => $nameUpdated,
        "email" => "plopez@demo.local.lan",
        "accounts" => []
      ]
    ];
    $result = $this->gqlClient->execute($query, $variables);
    $name = $result["data"]["user"]["name"] ?? null;
    return $name;
  }

  private function getDataUsersConnection()
  {
    $query = '
    query QueryUserConnection{
      connection: userConnection {
        totalCount
        edges {
          node{
            id
          }
        }
      }
    }
    ';
    $reuslt = $this->gqlClient->execute($query);
    return $reuslt["data"]["connection"];
  }
  private function getUserId($id)
  {
    $query = '
    query QueryUserItem($id: ID!){
      user: user(id: $id) {
        id
      }
    }
    ';
    $variables = ["id" => $id];
    $reuslt = $this->gqlClient->execute($query, $variables);
    return $reuslt["data"]["user"]["id"];
  }
  private function deleteUser(string $id): string
  {
    $query = '
    mutation MutationDeleteUser($id: ID!){
        user:deleteUser(id: $id) {
          id
        }
      }
    
    ';

    $variables = [
      "id" => $id,
    ];
    $result = $this->gqlClient->execute($query, $variables);
    $name = $result["data"]["user"]["id"] ?? null;
    return $name;
  }
}

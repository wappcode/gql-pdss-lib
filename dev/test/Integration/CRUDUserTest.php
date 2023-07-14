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
    $this->assertNotEmpty($id);
  }
}

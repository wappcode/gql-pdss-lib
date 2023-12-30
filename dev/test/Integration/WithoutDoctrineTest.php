<?php

use GQLBasicClient\GQLClient;

class WithoutDoctrineTest extends \PHPUnit\Framework\TestCase
{


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
        global $gqlClient;
        $this->gqlClient = $gqlClient;
    }

    public function testEchoUser()
    {
        $message = "Hola Mundo Test No Doctrine";
        $query = "
            query {
                echo(message: \"{$message}\")
            }
        ";
        $result = $this->gqlClient->execute($query);

        $this->assertEquals($result["data"]["echo"], $message, "Testing echo without Doctrine");
    }
}

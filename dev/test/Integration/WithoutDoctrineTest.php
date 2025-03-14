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

    public function testEcho()
    {
        $message = "Hola Mundo Test No Doctrine";
        $query = "
            query {
                echo(msg: \"{$message}\")
            }
        ";
        $result = $this->gqlClient->execute($query);

        $this->assertEquals($result["data"]["echo"], $message, "Testing echo without Doctrine");
    }
    public function testEchoProxy()
    {
        $message = "Hola Mundo Test No Doctrine";
        $query = "
            query {
                echo: echoProxy(msg: \"{$message}\")
            }
        ";
        $result = $this->gqlClient->execute($query);
        $expected = "Proxy 1 " . $message;

        $this->assertEquals($result["data"]["echo"], $expected, "Testing echo without Doctrine applying one proxies");
    }
    public function testEchoProxies()
    {
        $message = "Hola Mundo Test No Doctrine";
        $query = "
            query {
                echo: echoProxies(msg: \"{$message}\")
            }
        ";
        $result = $this->gqlClient->execute($query);
        $expected = "Proxy 1 Proxy 2 " . $message;

        $this->assertEquals($result["data"]["echo"], $expected, "Testing echo without Doctrine applying multiple proxies");
    }
}

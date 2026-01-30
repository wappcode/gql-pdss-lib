<?php

use GPDCore\Contracts\AppConfigInterface;
use GPDCore\Core\AppConfig;
use PHPUnit\Framework\TestCase;

class AppConfigTest extends TestCase
{
    private AppConfig $config;

    protected function setUp(): void
    {
        // Reset singleton instance before each test using reflection
        $reflection = new ReflectionClass(AppConfig::class);
        $instance = $reflection->getProperty('instance');
        $instance->setAccessible(true);
        $instance->setValue(null, null);
        $instance->setAccessible(false);

        $this->config = AppConfig::getInstance();
    }

    public function testImplementsAppConfigInterface()
    {
        $this->assertInstanceOf(AppConfigInterface::class, $this->config);
    }

    public function testGetInstanceReturnsSameInstance()
    {
        $instance1 = AppConfig::getInstance();
        $instance2 = AppConfig::getInstance();

        $this->assertSame($instance1, $instance2);
    }

    public function testGetReturnsNullForNonExistentKey()
    {
        $result = $this->config->get('non_existent_key');

        $this->assertNull($result);
    }

    public function testGetReturnsDefaultValueForNonExistentKey()
    {
        $default = 'default_value';
        $result = $this->config->get('non_existent_key', $default);

        $this->assertEquals($default, $result);
    }

    public function testAddAddsConfiguration()
    {
        $config = ['key1' => 'value1', 'key2' => 'value2'];
        $result = $this->config->add($config);

        $this->assertSame($this->config, $result);
        $this->assertEquals('value1', $this->config->get('key1'));
        $this->assertEquals('value2', $this->config->get('key2'));
    }

    public function testAddOverwritesExistingConfiguration()
    {
        $this->config->add(['key' => 'original_value']);
        $this->config->add(['key' => 'new_value']);

        $this->assertEquals('new_value', $this->config->get('key'));
    }

    public function testAddMergesNestedArraysRecursively()
    {
        $initialConfig = [
            'database' => [
                'host' => 'localhost',
                'port' => 3306,
                'credentials' => [
                    'username' => 'root',
                    'password' => 'secret',
                ],
            ],
        ];

        $newConfig = [
            'database' => [
                'port' => 3307,
                'credentials' => [
                    'password' => 'new_secret',
                ],
            ],
        ];

        $this->config->add($initialConfig);
        $this->config->add($newConfig);

        $this->assertEquals('localhost', $this->config->get('database')['host']);
        $this->assertEquals(3307, $this->config->get('database')['port']);
        $this->assertEquals('root', $this->config->get('database')['credentials']['username']);
        $this->assertEquals('new_secret', $this->config->get('database')['credentials']['password']);
    }

    public function testAddReturnsConfigInstanceForChaining()
    {
        $result = $this->config
            ->add(['key1' => 'value1'])
            ->add(['key2' => 'value2']);

        $this->assertInstanceOf(AppConfig::class, $result);
        $this->assertEquals('value1', $this->config->get('key1'));
        $this->assertEquals('value2', $this->config->get('key2'));
    }

    public function testConfigSupportsMultipleDataTypes()
    {
        $config = [
            'string' => 'text',
            'int' => 123,
            'float' => 45.67,
            'bool' => true,
            'array' => [1, 2, 3],
            'null' => null,
        ];

        $this->config->add($config);

        $this->assertIsString($this->config->get('string'));
        $this->assertIsInt($this->config->get('int'));
        $this->assertIsFloat($this->config->get('float'));
        $this->assertIsBool($this->config->get('bool'));
        $this->assertIsArray($this->config->get('array'));
        $this->assertNull($this->config->get('null'));
    }

    public function testMasterConfigHasPriority()
    {
        $this->config->add(['key' => 'normal_value']);
        $this->config->setMasterConfig(['key' => 'master_value']);

        $this->assertEquals('master_value', $this->config->get('key'));
    }

    public function testMasterConfigPreventsModificationByAdd()
    {
        $this->config->setMasterConfig(['key' => 'master_value']);
        $this->config->add(['key' => 'new_value']);

        $this->assertEquals('master_value', $this->config->get('key'));
    }

    public function testMasterConfigWorksWithNestedArrays()
    {
        $this->config->add([
            'database' => [
                'host' => 'localhost',
                'port' => 3306,
            ],
        ]);

        $this->config->setMasterConfig([
            'database' => [
                'port' => 9999,
            ],
        ]);

        $this->assertEquals(9999, $this->config->get('database')['port']);
        $this->assertEquals('localhost', $this->config->get('database')['host']);
    }

    public function testSetMasterConfigReturnsConfigForChaining()
    {
        $result = $this->config
            ->add(['key1' => 'value1'])
            ->setMasterConfig(['key2' => 'master2']);

        $this->assertInstanceOf(AppConfig::class, $result);
        $this->assertEquals('value1', $this->config->get('key1'));
        $this->assertEquals('master2', $this->config->get('key2'));
    }

    public function testMasterConfigMergesRecursively()
    {
        $this->config->setMasterConfig([
            'feature' => [
                'enabled' => true,
                'options' => ['debug' => false],
            ],
        ]);

        $this->config->setMasterConfig([
            'feature' => [
                'options' => ['verbose' => true],
            ],
        ]);

        $this->assertTrue($this->config->get('feature')['enabled']);
        $this->assertFalse($this->config->get('feature')['options']['debug']);
        $this->assertTrue($this->config->get('feature')['options']['verbose']);
    }
}

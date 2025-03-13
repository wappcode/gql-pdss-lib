<?php

namespace GPDCore\Services;

use DateTime;
use Exception;
use DateTimeImmutable;
use DateTimeInterface;
use GraphQL\Doctrine\Types;
use Doctrine\ORM\EntityManager;
use GPDCore\Graphql\Types\DateType;
use GPDCore\Graphql\Types\JSONData;
use GPDCore\Library\IContextService;
use GPDCore\Graphql\Types\DateTimeType;
use GPDCore\Graphql\Types\QueryJoinType;
use GPDCore\Graphql\Types\QuerySortType;
use GPDCore\Factory\EntityManagerFactory;
use GPDCore\Graphql\ConnectionTypeFactory;
use GPDCore\Graphql\Types\ConnectionInput;
use GPDCore\Graphql\Types\QueryFilterType;
use Laminas\ServiceManager\ServiceManager;
use GPDCore\Graphql\Types\QueryFilterLogic;
use GPDCore\Graphql\Types\QueryJoinTypeValue;
use GPDCore\Graphql\Types\QuerySortDirection;
use GPDCore\Graphql\Types\DateTimeImmutableType;
use GPDCore\Graphql\Types\ListInput;
use GPDCore\Graphql\Types\PageInfoType;
use GPDCore\Graphql\Types\PaginationInput;
use GPDCore\Graphql\Types\QueryFilterConditionType;
use GPDCore\Graphql\Types\QueryFilterConditionTypeValue;
use GPDCore\Graphql\Types\QueryFilterConditionValueType;

class ContextService implements IContextService
{



    const SM_DATETIME = 'DateTime';
    const SM_DATE = 'Date';
    const SM_ENTITY_MANAGER = 'entityManager';
    const SM_CONFIG = 'config';



    /**
     * @var EntityManager
     */
    protected $entityManager;

    /**
     * @var ?Types
     */
    protected $types;

    /**
     * Determina si la app se esta ejecutando en modo producción
     *
     * @var bool
     */
    protected $productionMode;
    protected $enviroment;

    protected $doctrineConfigFile = __DIR__ . "/../../../../../../config/doctrine.local.php";
    protected $doctrineCacheDir = __DIR__ . "/../../../../../../data/DoctrineORMModule";
    protected $hasBeenInitialized = false;
    protected $withoutDoctrine = false;

    /**
     * @var ServiceManager
     */
    protected $serviceManager;

    public function __construct(ServiceManager $serviceManager)
    {
        $this->serviceManager = $serviceManager;
    }
    public function init(string $enviroment, bool $productionMode, bool $withoutDoctrine = false): void
    {
        if ($this->hasBeenInitialized) {
            throw new Exception("Context can be initialized just once");
        }
        $this->enviroment = $enviroment;
        $this->productionMode = $productionMode;
        if (!$withoutDoctrine) {
            $this->setEntityManager();
            $this->addTypes();
        }
        $this->hasBeenInitialized = true;
    }

    public function getEntityManager(): ?EntityManager
    {
        return $this->entityManager;
    }
    public function getConfig(): ConfigService
    {
        return ConfigService::getInstance();
    }
    public function getServiceManager(): ServiceManager
    {
        return $this->serviceManager;
    }
    protected function setEntityManager()
    {

        $configFile = $this->doctrineConfigFile;
        if (file_exists($configFile)) {
            $options = require $configFile;
        } else {
            return [];
        }
        $isDevMode = !$this->productionMode;
        $this->entityManager = EntityManagerFactory::createInstance($options, $this->doctrineCacheDir, $isDevMode);
    }

    protected function addTypes()
    {
        $this->addInvokablesToServiceManager();
        $this->addFactoriesToServiceManager();
        $this->addAliasesToServiceManager();
    }

    protected function addInvokablesToServiceManager()
    {
        $this->serviceManager->setInvokableClass(static::SM_DATETIME,  DateTimeType::class);
        $this->serviceManager->setInvokableClass(static::SM_DATE,  DateType::class);
        $this->serviceManager->setInvokableClass(JSONData::class,  JSONData::class);
    }

    protected function addFactoriesToServiceManager() {}
    protected function addAliasesToServiceManager()
    {
        $this->serviceManager->setAlias(static::SM_DATETIME, DateTime::class); // Declare alias for Doctrine type to be used for filters
        $this->serviceManager->setAlias(static::SM_DATE, DateType::class); // Declare alias for Doctrine type to be used for filters
        $this->serviceManager->setAlias(JSONData::SM_NAME,  JSONData::class);
    }

    /**
     * Get the value of configFile
     * @deprecated version 2.0.13 usar getDoctrineConfigFile
     */
    public function getConfigFile()
    {
        // @todo remover version 3
        return $this->getDoctrineConfigFile();
    }

    /**
     * Set the value of configFile
     *
     * @return  self
     * @deprecated version 2.0.13 usar setDoctrineConfigFile
     */
    public function setConfigFile($configFile)
    {
        // @todo remover version 3
        return $this->setDoctrineConfigFile($configFile);
    }

    /**
     * Get the value of cacheDir
     * @deprecated version 2.0.13 usar getDoctrineCacheDir
     */
    public function getCacheDir()
    {
        return $this->getDoctrineCacheDir();
    }

    /**
     * Set the value of cacheDir
     *
     * @return  self
     * @deprecated version 2.0.13 usar getDoctrineCacheDir
     */
    public function setCacheDir($cacheDir)
    {
        return $this->setDoctrineCacheDir($cacheDir);
    }

    /**
     * Get determina si la app se esta ejecutando en modo producción
     *
     * @return  bool
     */
    public function isProductionMode(): bool
    {
        return $this->productionMode;
    }

    /**
     * Get the value of doctrineConfigFile
     */
    public function getDoctrineConfigFile()
    {
        return $this->doctrineConfigFile;
    }

    /**
     * Set the value of doctrineConfigFile
     *
     * @return  self
     */
    public function setDoctrineConfigFile($doctrineConfigFile)
    {
        $this->doctrineConfigFile = $doctrineConfigFile;

        return $this;
    }

    /**
     * Get the value of doctrineCacheDir
     */
    public function getDoctrineCacheDir()
    {
        return $this->doctrineCacheDir;
    }

    /**
     * Set the value of doctrineCacheDir
     *
     * @return  self
     */
    public function setDoctrineCacheDir($doctrineCacheDir)
    {
        $this->doctrineCacheDir = $doctrineCacheDir;

        return $this;
    }
}

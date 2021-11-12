<?php

namespace GPDCore\Services;

use DateTime;
use GraphQL\Doctrine\Types;
use Doctrine\ORM\EntityManager;
use GPDCore\Library\IContextService;
use Doctrine\DBAL\Types\DateTimeType;
use GPDCore\Graphql\Types\QueryJoinType;
use GPDCore\Graphql\Types\QuerySortType;
use GPDCore\Factory\EntityManagerFactory;
use GPDCore\Graphql\ConnectionTypeFactory;
use GPDCore\Graphql\Types\QueryFilterType;
use Laminas\ServiceManager\ServiceManager;
use GPDCore\Graphql\Types\QueryFilterLogic;
use GPDCore\Graphql\Types\QueryJoinTypeValue;
use GPDCore\Graphql\Types\QuerySortDirection;
use GPDCore\Graphql\Types\QueryFilterConditionType;
use GPDCore\Graphql\Types\QueryFilterConditionTypeValue;

class ContextService implements IContextService
{



    const SM_PAGE_INFO = 'PageInfo';
    const SM_PAGE_INFO_INPUT = 'PaginationInput';
    const SM_DATETIME = 'datetime';
    const SM_DATE = 'date';
    const SM_ENTITY_MANAGER = 'entityManager';
    const SM_CONFIG = 'config';

    /**
     * @var EntityManager
     */
    protected $entityManager;

    /**
     * @var Types
     */
    protected $types;

    protected $isProductionMode;


    /**
     * @var ServiceManager
     */
    protected $serviceManager;

    public function __construct(ServiceManager $serviceManager, bool $isProductionMode)
    {
        $this->serviceManager = $serviceManager;
        $this->isProductionMode = $isProductionMode;
        $this->setEntityManager();
        $this->setTypes();
        $this->addTypes();
    }

    public function getEntityManager(): EntityManager
    {
        return $this->entityManager;
    }
    public function getConfig(): ConfigService
    {
        return ConfigService::getInstance();
    }
    public function getTypes(): Types
    {
        return $this->types;
    }
    public function getServiceManager(): ServiceManager
    {
        return $this->serviceManager;
    }
    protected function setEntityManager()
    {

        $options = require __DIR__ . "/../../../../../../../../config/doctrine.local.php";
        $proxyDir =  __DIR__ . "/../../../../../../../../data/DoctrineORMModule/Proxy";
        $isDevMode = !$this->isProductionMode;
        $this->entityManager = EntityManagerFactory::createInstance($options, $proxyDir, $isDevMode);
    }
    protected function setTypes()
    {
        TypesService::init($this->entityManager, $this->serviceManager);
        $this->types = TypesService::getInstance();
    }

    protected function addTypes()
    {
        $this->addInvokablesToServiceManager();
        $this->addFactoriesToServiceManager();
        $this->addAliasesToServiceManager();
    }

    protected function addInvokablesToServiceManager()
    {
        $this->serviceManager->setInvokableClass(DateTime::class,  DateTimeType::class);
        $this->serviceManager->setInvokableClass(QueryFilterLogic::class,  QueryFilterLogic::class);
        $this->serviceManager->setInvokableClass(QueryFilterConditionTypeValue::class,  QueryFilterConditionTypeValue::class);
        $this->serviceManager->setInvokableClass(QuerySortDirection::class,  QuerySortDirection::class);
        $this->serviceManager->setInvokableClass(QueryJoinTypeValue::class,  QueryJoinTypeValue::class);
    }

    protected function addFactoriesToServiceManager()
    {
        $this->serviceManager->setFactory(static::SM_PAGE_INFO, function () {
            return ConnectionTypeFactory::getPageInfoType();
        });
        $this->serviceManager->setFactory(static::SM_PAGE_INFO_INPUT, function () {
            return ConnectionTypeFactory::getPaginationInput();
        });
        $this->serviceManager->setFactory(QueryFilterConditionType::class, function ($sm) {
            return new QueryFilterConditionType($sm);
        });
        $this->serviceManager->setFactory(QueryFilterType::class, function ($sm) {
            return new QueryFilterType($sm);
        });
        $this->serviceManager->setFactory(QuerySortType::class, function ($sm) {
            return new QuerySortType($sm);
        });
        $this->serviceManager->setFactory(QueryJoinType::class, function ($sm) {
            return new QueryJoinType($sm);
        });
    }
    protected function addAliasesToServiceManager()
    {
        $this->serviceManager->setAlias(static::SM_DATETIME, DateTime::class); // Declare alias for Doctrine type to be used for filters
        $this->serviceManager->setAlias(static::SM_DATE, DateTime::class); // Declare alias for Doctrine type to be used for filters
    }
}

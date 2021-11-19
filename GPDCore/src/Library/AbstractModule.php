<?php

declare(strict_types=1);

namespace GPDCore\Library;

use GraphQL\Doctrine\Types;


abstract class AbstractModule {


    /**
     * @var IContextService
     */
    protected $context;
    
    public function setContext(IContextService $context) {
        $this->context = $context;
    }
    /**
     * Array con la configuración del módulo
     *
     * @return array
     */
    abstract function getConfig(): array;
    
    /**
     * Array con los tipos graphql que se necesitan para el módulo
     * El indice se utiliza como nombre del tipo
     *
     * @return array array(string $key => $type)
     */
    abstract function getGQLTypes(): array;
    
    /**
     * Array con los resolvers del módulo
     *
     * @return array array(string $key => callable $resolver)
     */
    abstract function getResolvers(): array;
    /**
     * Array con los graphql Queries del módulo
     *
     * @return array
     */
    abstract function getQueryFields(): array;
    /**
     * Array con los graphql mutations del módulo
     *
     * @return array
     */
    abstract function getMutationFields(): array;
   

    
}
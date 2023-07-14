<?php

declare(strict_types=1);

namespace GPDCore\Library;

class QueryDecorator
{
    private $decorator;


    /**
     * Retorna una funcion con la siguiente definición
     * function(QueryBuilder $qb, array $root, array $args, IContextService, $context, $info): QueryBuilder { ...codigo para modificar query}
     * 
     */
    public function getDecorator(): ?callable
    {
        return $this->decorator;
    }

    /**
     * Establece una funcion con la siguiente definición
     * function(QueryBuilder $qb, array $root, array $args, IContextService, $context, $info): QueryBuilder{ ...codigo para modificar query}
     *
     * @return  self
     */
    public function setDecorator(?callable $decorator)
    {
        $this->decorator = $decorator;

        return $this;
    }
}

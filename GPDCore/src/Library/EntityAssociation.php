<?php

namespace GPDCore\Library;

final class EntityAssociation
{
    /**
     * Nombre de la relación
     *
     * @var string
     */
    private $fieldName;
    /**
     * Calse de la Entidad Relacionada 
     *
     * @var string
     */
    private $targetEntity;
    /**
     * Nombre de la propiedad primary key de la asociación 
     *
     * @var string
     */
    private $identifier;


    public function __construct(string $fieldName,  string $identifier, string $targetEntity)
    {
        $this->fieldName = $fieldName;
        $this->targetEntity = $targetEntity;
        $this->identifier = $identifier;
    }
    /**
     * Get nombre de la relación
     *
     * @return  string
     */
    public function getFieldName()
    {
        return $this->fieldName;
    }

    /**
     * Set nombre de la relación
     *
     * @param  string  $fieldName  Nombre de la relación
     *
     * @return  self
     */
    public function setFieldName(string $fieldName)
    {
        $this->fieldName = $fieldName;

        return $this;
    }

    /**
     * Get calse de la Entidad Relacionada
     *
     * @return  string
     */
    public function getTargetEntity()
    {
        return $this->targetEntity;
    }

    /**
     * Set calse de la Entidad Relacionada
     *
     * @param  string  $targetEntity  Calse de la Entidad Relacionada
     *
     * @return  self
     */
    public function setTargetEntity(string $targetEntity)
    {
        $this->targetEntity = $targetEntity;

        return $this;
    }

    /**
     * Get nombre de la propiedad primary key de la asociación
     *
     * @return  string
     */
    public function getIdentifier()
    {
        return $this->identifier;
    }

    /**
     * Set nombre de la propiedad primary key de la asociación
     *
     * @param  string  $identifier  Nombre de la propiedad primary key de la asociación
     *
     * @return  self
     */
    public function setIdentifier(string $identifier)
    {
        $this->identifier = $identifier;

        return $this;
    }
}

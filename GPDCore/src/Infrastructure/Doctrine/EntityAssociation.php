<?php

declare(strict_types=1);

namespace GPDCore\Infrastructure\Doctrine;

final class EntityAssociation
{
    /**
     * Nombre de la relación.
     *
     * @var string
     */
    private string $fieldName;

    /**
     * Calse de la Entidad Relacionada.
     *
     * @var string
     */
    private string $targetEntity;

    /**
     * Nombre de la propiedad primary key de la asociación.
     *
     * @var string
     */
    private string $identifier;

    public function __construct(string $fieldName, string $identifier, string $targetEntity)
    {
        $this->fieldName = $fieldName;
        $this->targetEntity = $targetEntity;
        $this->identifier = $identifier;
    }

    public function getFieldName(): string
    {
        return $this->fieldName;
    }

    public function setFieldName(string $fieldName): self
    {
        $this->fieldName = $fieldName;

        return $this;
    }

    public function getTargetEntity(): string
    {
        return $this->targetEntity;
    }

    public function setTargetEntity(string $targetEntity): self
    {
        $this->targetEntity = $targetEntity;

        return $this;
    }

    public function getIdentifier(): string
    {
        return $this->identifier;
    }

    public function setIdentifier(string $identifier): self
    {
        $this->identifier = $identifier;

        return $this;
    }
}

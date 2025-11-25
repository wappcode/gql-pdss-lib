<?php

declare(strict_types=1);

namespace GPDCore\Entities;

use DateTimeImmutable;
use Doctrine\ORM\Mapping as ORM;
use GraphQL\Doctrine\Annotation as API;
use PDSSUtilities\DoctrineUniqueIDStringGenerator;

/**
 * Base class for all objects stored in database. ID type string.
 */
#[ORM\MappedSuperclass]

abstract class AbstractEntityModelStringId
{
    #[ORM\Id]
    #[ORM\Column(name: 'id', type: 'string', length: 255)]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\CustomIdGenerator(class: DoctrineUniqueIDStringGenerator::class)]
    protected $id;

    #[ORM\Column(type: 'datetimetz_immutable')]
    protected DateTimeImmutable $created;

    #[ORM\Column(type: 'datetimetz_immutable')]
    protected DateTimeImmutable $updated;

    public function __construct()
    {
        $this->created = new DateTimeImmutable();
        $this->updated = new DateTimeImmutable();
    }

    public function getId(): ?string
    {
        return $this->id;
    }

    /**
     * Get the value of created.
     *
     * @API\Field(type="DateTime")
     */
    public function getCreated(): DateTimeImmutable
    {
        return $this->created;
    }

    /**
     * Get the value of updated.
     *
     * @API\Field(type="DateTime")
     */
    public function getUpdated(): DateTimeImmutable
    {
        return $this->updated;
    }

    /**
     * Set the value of updated.
     *
     * @API\Exclude
     *
     * @return self
     */
    public function setUpdated()
    {
        $this->updated = new DateTimeImmutable();

        return $this;
    }
}

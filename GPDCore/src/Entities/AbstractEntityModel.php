<?php


declare(strict_types=1);

namespace GPDCore\Entities;

use DateTimeImmutable;
use Doctrine\ORM\Mapping as ORM;
use GraphQL\Doctrine\Annotation as API;

/**
 * Base class for all objects stored in database. ID type integer
 *
 * @ORM\MappedSuperclass
 */
abstract class AbstractEntityModel
{
    /**
     * @var int
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @ORM\Column(type="integer")
     * 
     */
    protected $id;

    /**
     * @var DateTimeImmutable
     * @ORM\Column(type="datetimetz_immutable")
     */
    protected $created;

    /**
     * @var DateTimeImmutable
     * @ORM\Column(type="datetimetz_immutable")
     */
    protected $updated;

    public function __construct()
    {
        $this->created = new DateTimeImmutable();
        $this->updated = new DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }
    /**
     * Get the value of created
     *
     * @API/Field(type="DateTime")
     * @return  DateTimeImmutable
     */
    public function getCreated(): DateTimeImmutable
    {
        return $this->created;
    }

    /**
     * Get the value of updated
     * @API/Field(type="DateTime")
     * @return  DateTimeImmutable
     */
    public function getUpdated(): DateTimeImmutable
    {
        return $this->updated;
    }

    /**
     * Set the value of updated
     *
     * @API\Exclude
     * 
     *
     * @return  self
     */
    public function setUpdated()
    {
        $this->updated = new DateTimeImmutable();

        return $this;
    }
}

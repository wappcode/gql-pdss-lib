<?php


declare(strict_types=1);

namespace GPDCore\Entities;

use DateTime;
use Doctrine\ORM\Mapping as ORM;
use GraphQL\Doctrine\Annotation as API;

/**
 * Base class for all objects stored in database. ID type string.
 *
 * @ORM\MappedSuperclass
 */
abstract class AbstractEntityModelGUID
{
    /**
     * @var string
     * @ORM\Column(type="guid")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="UUID")
     */
    protected $id;

    /**
     * @var DateTime
     * @ORM\Column(type="datetime")
     */
    protected $created;

    /**
     * @var DateTime
     * @ORM\Column(type="datetime")
     */
    protected $updated;

    public function __construct()
    {
        $this->created = new DateTime();
        $this->updated = new DateTime();
    }

    public function getId(): ?string
    {
        return $this->id;
    }
    /**
     * Get the value of created
     *
     * @return  DateTime
     */
    public function getCreated(): DateTime
    {
        return $this->created;
    }

    /**
     * Get the value of updated
     *
     * @return  DateTime
     */
    public function getUpdated(): DateTime
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
        $this->updated = new DateTime();

        return $this;
    }
}

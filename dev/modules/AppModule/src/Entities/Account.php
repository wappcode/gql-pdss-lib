<?php

namespace AppModule\Entities;

use DateTimeImmutable;
use Doctrine\ORM\Mapping as ORM;
use GraphQL\Doctrine\Annotation as API;
use GPDCore\Entities\AbstractEntityModel;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * @ORM\Entity()
 * @ORM\Table(name="account")
 */
class Account
{

    /**
     * @ORM\Id
     * @ORM\Column(name="code", type="string")
     * @ORM\GeneratedValue(strategy="CUSTOM")
     * @ORM\CustomIdGenerator(class="\PDSSUtilities\DoctrineUniqueIDStringGenerator")
     * @var string
     */
    protected $code;
    /**
     * @ORM\Column(type="string",nullable=false)
     *
     * @var string
     */
    private $title;


    /**
     * @ORM\ManyToMany(targetEntity="\AppModule\Entities\User", mappedBy="accounts", cascade={"persist","remove"}, orphanRemoval=true)
     *
     * @var Collection
     */
    private $users;

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
        $this->users = new ArrayCollection();
    }


    /**
     * Get the value of title
     *
     * @return  string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * Set the value of title
     *
     * @param  string  $title
     *
     * @return  self
     */
    public function setTitle(string $title)
    {
        $this->title = $title;

        return $this;
    }

    /**
     * Get the value of users
     *
     * @return  Collection
     */
    public function getUsers(): Collection
    {
        return $this->users;
    }

    /**
     * Set the value of users
     *
     * @API\Input(type="id[]")
     * @param  Collection  $users
     *
     * @return  self
     */
    public function setUsers(Collection $users)
    {
        $this->users = $users;

        return $this;
    }

    /**
     * Get the value of created
     *
     * @return  DateTimeImmutable
     */
    public function getCreated()
    {
        return $this->created;
    }

    /**
     * Set the value of created
     *
     * @param  DateTimeImmutable  $created
     *
     * @return  self
     */
    public function setCreated(DateTimeImmutable $created)
    {
        $this->created = $created;

        return $this;
    }

    /**
     * Get the value of updated
     *
     * @return  DateTimeImmutable
     */
    public function getUpdated()
    {
        return $this->updated;
    }

    /**
     * Set the value of updated
     *
     * @param  DateTimeImmutable  $updated
     *
     * @return  self
     */
    public function setUpdated(DateTimeImmutable $updated)
    {
        $this->updated = $updated;

        return $this;
    }

    /**
     * Get the value of code
     *
     * @return  string
     */
    public function getCode()
    {
        return $this->code;
    }
}

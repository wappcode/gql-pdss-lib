<?php

namespace AppModule\Entities;

use DateTimeImmutable;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'account')]
class Account
{
    #[ORM\Id]
    #[ORM\Column(name: 'code', type: 'string', length: 255)]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\CustomIdGenerator(class: "\PDSSUtilities\DoctrineUniqueIDStringGenerator")]
    protected $code;

    #[ORM\Column(type: 'string', nullable: false)]
    protected $title;

    #[ORM\ManyToMany(targetEntity: User::class, mappedBy: 'accounts')]
    protected $users;

    #[ORM\Column(type: 'datetimetz_immutable')]
    protected $created;

    #[ORM\Column(type: 'datetimetz_immutable')]
    protected $updated;

    public function __construct()
    {
        $this->created = new DateTimeImmutable();
        $this->updated = new DateTimeImmutable();
        // $this->users = new ArrayCollection();
    }

    /**
     * Get the value of code.
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * Set the value of code.
     *
     * @return self
     */
    public function setCode($code)
    {
        $this->code = $code;

        return $this;
    }

    /**
     * Get the value of title.
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * Set the value of title.
     *
     * @return self
     */
    public function setTitle($title)
    {
        $this->title = $title;

        return $this;
    }

    // /**
    //  * Get the value of users
    //  */
    // public function getUsers()
    // {
    //     return $this->users;
    // }

    // /**
    //  * Set the value of users
    //  *
    //  * @return  self
    //  */
    // public function setUsers($users)
    // {
    //     $this->users = $users;

    //     return $this;
    // }

    /**
     * Get the value of created.
     */
    public function getCreated()
    {
        return $this->created;
    }

    /**
     * Set the value of created.
     *
     * @return self
     */
    public function setCreated($created)
    {
        $this->created = $created;

        return $this;
    }

    /**
     * Get the value of updated.
     */
    public function getUpdated()
    {
        return $this->updated;
    }

    /**
     * Set the value of updated.
     *
     * @return self
     */
    public function setUpdated($updated)
    {
        $this->updated = $updated;

        return $this;
    }

    /**
     * Get the value of users.
     */
    public function getUsers()
    {
        return $this->users;
    }

    /**
     * Set the value of users.
     *
     * @return self
     */
    public function setUsers($users)
    {
        $this->users = $users;

        return $this;
    }
}

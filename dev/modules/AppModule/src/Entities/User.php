<?php

namespace AppModule\Entities;

use Doctrine\ORM\Mapping as ORM;
use GraphQL\Doctrine\Annotation as API;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;
use GPDCore\Entities\AbstractEntityModelStringId;

/**
 * @ORM\Entity()
 * @ORM\Table(name="users")
 */
class User extends AbstractEntityModelStringId
{


    /**
     * @ORM\Column(type="string", length=255)
     *
     * @var string
     */
    private $name;
    /**
     * @ORM\Column(type="string")
     *
     * @var string
     */
    private $email;


    /**
     * @ORM\ManyToMany(targetEntity="\AppModule\Entities\Account")
     * @ORM\JoinTable(name="users_accounts", joinColumns={
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id")
     * },
     * inverseJoinColumns={@ORM\JoinColumn(name="account_code", referencedColumnName="code")}
     * )
     *
     * @var Collection
     */
    private $accounts;


    /**
     * @ORM\OneToMany(targetEntity="\AppModule\Entities\Post", mappedBy="user",  cascade={"persist","remove"}, orphanRemoval=true)
     *
     * @var Collection
     */
    private $posts;


    public function __construct()
    {
        parent::__construct();
        $this->accounts = new ArrayCollection();
        $this->posts = new ArrayCollection();
    }
    /**
     * Get the value of name
     *
     * @return  string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set the value of name
     *
     * @param  string  $name
     *
     * @return  self
     */
    public function setName(string $name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get the value of email
     *
     * @return  string
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * Set the value of email
     *
     * @param  string  $email
     *
     * @return  self
     */
    public function setEmail(string $email)
    {
        $this->email = $email;

        return $this;
    }

    /**
     * Get )
     *
     * @return  Collection
     */
    public function getAccounts(): Collection
    {
        return $this->accounts;
    }

    /**
     * Set )
     *
     * @API\Input(type="id[]")
     * @param  Collection  $accounts  )
     *
     * @return  self
     */
    public function setAccounts(Collection $accounts)
    {
        $this->accounts = $accounts;

        return $this;
    }

    /**
     * Get the value of posts
     *
     * @return  Collection
     */
    public function getPosts(): Collection
    {
        return $this->posts;
    }

    /**
     * Set the value of posts
     *
     * @API\Exclude
     * @param  Collection  $posts
     *
     * @return  self
     */
    public function setPosts(Collection $posts)
    {
        $this->posts = $posts;

        return $this;
    }
}

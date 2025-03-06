<?php

namespace AppModule\Entities;

use Doctrine\ORM\Mapping as ORM;
use GraphQL\Doctrine\Annotation as API;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;
use GPDCore\Entities\AbstractEntityModelStringId;

#[ORM\Entity()]
#[ORM\Table(name: "users")]
class User extends AbstractEntityModelStringId
{


    #[ORM\Column(type: "string", length: 255)]
    private string $name;
    #[ORM\Column(type: "string", length: 255)]
    private string $email;


    #[ORM\JoinTable(name: "users_accounts")]
    #[ORM\JoinColumn(name: "user_id", referencedColumnName: "id", columnDefinition: "VARCHAR(255) NOT NULL")]
    #[ORM\InverseJoinColumn(name: 'account_code', referencedColumnName: 'code', columnDefinition: "VARCHAR(255) NOT NULL")]
    #[ORM\ManyToMany(targetEntity: Account::class)]
    private Collection $accounts;


    #[ORM\OneToMany(targetEntity: Post::class, mappedBy: "user")]
    private $posts;


    public function __construct()
    {
        parent::__construct();
        $this->accounts = new ArrayCollection();
        $this->posts = new ArrayCollection();
    }
    public function getName()
    {
        return $this->name;
    }

    public function setName(string $name)
    {
        $this->name = $name;

        return $this;
    }

    public function getEmail()
    {
        return $this->email;
    }

    public function setEmail(string $email)
    {
        $this->email = $email;

        return $this;
    }

    public function getAccounts(): Collection
    {
        return $this->accounts;
    }

    public function setAccounts(Collection $accounts)
    {
        $this->accounts = $accounts;

        return $this;
    }

    public function getPosts(): Collection
    {
        return $this->posts;
    }

    public function setPosts(Collection $posts)
    {
        $this->posts = $posts;

        return $this;
    }
}

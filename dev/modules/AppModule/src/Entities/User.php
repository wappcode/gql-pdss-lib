<?php

namespace AppModule\Entities;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use PDSSUtilities\AbstractEntityModelUlid;

class User extends AbstractEntityModelUlid
{
    private string $name;

    private string $email;

    private Collection $accounts;

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

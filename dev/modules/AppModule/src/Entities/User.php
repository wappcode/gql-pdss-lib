<?php

namespace AppModule\Entities;

use GPDCore\Entities\AbstractEntityModelStringId;
use Doctrine\ORM\Mapping as ORM;
use GraphQL\Doctrine\Annotation as API;

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
     * inverseJoinColumns={@ORM\JoinColumn(name="account_id", referencedColumnName="id")}
     * )
     *
     * @var Account
     */
    private $accounts;

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
     * @return  Account
     */
    public function getAccounts()
    {
        return $this->accounts;
    }

    /**
     * Set )
     *
     * @param  Account  $accounts  )
     *
     * @return  self
     */
    public function setAccounts(Account $accounts)
    {
        $this->accounts = $accounts;

        return $this;
    }
}

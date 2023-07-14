<?php

namespace AppModule\Entities;

use Doctrine\Common\Collections\ArrayCollection;
use GPDCore\Entities\AbstractEntityModel;
use Doctrine\ORM\Mapping as ORM;
use GraphQL\Doctrine\Annotation as API;

/**
 * @ORM\Entity()
 * @ORM\Table(name="account")
 */
class Account extends AbstractEntityModel
{

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

    public function __construct()
    {
        parent::__construct();
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
}

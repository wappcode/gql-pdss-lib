<?php

namespace AppModule\Entities;

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

<?php

namespace AppModule\Entities;

use AppModule\Entities\User;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use GraphQL\Doctrine\Annotation as API;
use GPDCore\Entities\AbstractEntityModel;
use Doctrine\Common\Collections\Collection;

#[ORM\Entity()]
#[ORM\Table(name: "post")]
class Post extends AbstractEntityModel
{



    #[ORM\Column(type: "string", length: 255, nullable: false)]
    private string $title;

    #[ORM\Column(type: "text", nullable: false)]
    private string $body;

    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: "user")]
    #[ORM\JoinColumn(name: "author_id", referencedColumnName: "id", columnDefinition: "VARCHAR(255) NOT NULL")]

    private User $author;

    #[ORM\OneToMany(targetEntity: Comment::class, mappedBy: "post")]
    private Collection $comments;

    public function __construct()
    {
        parent::__construct();
        $this->comments = new ArrayCollection();
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
     * Get the value of body
     *
     * @return  string
     */
    public function getBody()
    {
        return $this->body;
    }

    /**
     * Set the value of body
     *
     * @param  string  $body
     *
     * @return  self
     */
    public function setBody(string $body)
    {
        $this->body = $body;

        return $this;
    }

    /**
     * Get the value of author
     *
     * @return  User
     */
    public function getAuthor()
    {
        return $this->author;
    }

    /**
     * Set the value of author
     *
     * @param  User  $author
     *
     * @return  self
     */
    public function setAuthor(User $author)
    {
        $this->author = $author;

        return $this;
    }

    /**
     * Get the value of comments
     *
     * @return  Collection
     */
    public function getComments(): Collection
    {
        return $this->comments;
    }

    /**
     * Set the value of comments
     *
     * @API\Exclude
     * @param  Collection  $comments
     *
     * @return  self
     */
    public function setComments(Collection $comments)
    {
        $this->comments = $comments;

        return $this;
    }
}

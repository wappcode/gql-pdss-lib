<?php

namespace AppModule\Entities;

use AppModule\Entities\Post;
use Doctrine\ORM\Mapping as ORM;
use GraphQL\Doctrine\Annotation as API;
use GPDCore\Entities\AbstractEntityModel;

/**
 * @ORM\Entity()
 * @ORM\Table(name="comments")
 */
class Comment extends AbstractEntityModel
{

    /**
     * @ORM\Column(type="TEXT",nullable = false)
     * @var String
     */

    private $text;

    /**
     * 
     * @ORM\ManyToOne(targetEntity="\AppModule\Entities\Post", inversedBy="comments")
     * @ORM\JoinColumn(name="post_id", referencedColumnName="id")
     * @var Post
     */
    private $post;


    /**
     * Get the value of text
     */
    public function getText()
    {
        return $this->text;
    }

    /**
     * Set the value of text
     *
     * @return  self
     */
    public function setText($text)
    {
        $this->text = $text;

        return $this;
    }

    /**
     * Get the value of post
     *
     * @return  Post
     */
    public function getPost()
    {
        return $this->post;
    }

    /**
     * Set the value of post
     *
     * @param  Post  $post
     *
     * @return  self
     */
    public function setPost(Post $post)
    {
        $this->post = $post;

        return $this;
    }
}

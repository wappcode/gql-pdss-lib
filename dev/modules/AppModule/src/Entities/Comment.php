<?php

namespace AppModule\Entities;

use Doctrine\ORM\Mapping as ORM;
use GPDCore\Entities\AbstractEntityModel;

#[ORM\Entity()]
#[ORM\Table(name: 'comments')]
class Comment extends AbstractEntityModel
{
    #[ORM\Column(type: 'text', nullable: false)]
    private string $text;

    #[ORM\ManyToOne(targetEntity: Post::class, inversedBy: 'comments')]
    #[ORM\JoinColumn(name: 'post_id', referencedColumnName: 'id')]
    private Post $post;

    /**
     * Get the value of text.
     */
    public function getText(): string
    {
        return $this->text;
    }

    /**
     * Set the value of text.
     *
     * @return self
     */
    public function setText(string $text)
    {
        $this->text = $text;

        return $this;
    }

    /**
     * Get the value of post.
     *
     * @return Post
     */
    public function getPost()
    {
        return $this->post;
    }

    /**
     * Set the value of post.
     *
     * @return self
     */
    public function setPost(Post $post)
    {
        $this->post = $post;

        return $this;
    }
}

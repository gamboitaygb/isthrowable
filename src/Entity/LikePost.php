<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\LikePostRepository")
 */
class LikePost
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\User")
     */
    private $user;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Post")
     */
    private $post;

    /**
     * @ORM\Column(type="boolean")
     */
    private $likep;

    public function __construct()
    {
        $this->post=null;
        $this->user=null;
    }



    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUser()
    {
        return $this->user;
    }

    public function setUser(\App\Entity\User $user)
    {
        $this->user = $user;

        return $this;
    }

    public function getPost()
    {
        return $this->post;
    }

    public function setPost(\App\Entity\Post $post)
    {
        $this->post = $post;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getLikep()
    {
        return $this->likep;
    }

    /**
     * @param mixed $like
     */
    public function setLikep($like)
    {
        $this->likep = $like;
    }

}

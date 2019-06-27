<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use App\Utils\Slugger;
use App\Utils\Loginrss;
use phpDocumentor\Reflection\Types\Self_;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\File;

/**
 * @ORM\Entity(repositoryClass="App\Repository\PostRepository")
 */
class Post
{
    const MEDIA_SERVER = 'cdn.isthrowable.com';
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255,unique=true)
     */
    private $title;

    /**
     *
     * @ORM\Column(type="string", length=255)
     */
    private $slug;

    /**
     * @ORM\Column(type="text")
     */
    private $content;

    /**
     * @ORM\Column(type="datetime")
     */
    private $date_created;

    /**
     * @ORM\Column(type="datetime")
     */
    private $date_upd;


    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Person")
     */
    private $author;

    /**
     * @ORM\Column(type="string", length=128,nullable=true)
     */
    private $picture;


    /**
     * @Assert\Image(maxSize = "500k")
     */
    private $photoPath;


    /**
     * @ORM\Column(type="boolean")
     */
    private $active;

    /**
     * @ORM\Column(type="boolean")
     */
    private $new;

    /**
     * @var int
     *
     * @ORM\Column(name="views", type="integer")
     */
    private $views;

    /**
     * @var int
     *
     * @ORM\Column(name="likes", type="integer")
     */
    private $like;




    public function getId()
    {
        return $this->id;
    }

    public function getTitle()
    {
        return $this->title;
    }

    public function setTitle($title)
    {
        $this->title = $title;
        $this->slug = Slugger::getSlug($title);

        return $this;
    }


    /**
     * Set slug
     *
     * @param string $slug
     *
     * @return Post
     */
    public function setSlug($slug)
    {
        $this->slug = $slug;

        return $this;
    }

    /**
     * Get slug
     *
     * @return string
     */
    public function getSlug()
    {
        return $this->slug;
    }

    public function getContent()
    {
        return $this->content;
    }

    public function setContent($content)
    {
        $this->content = $content;

        return $this;
    }

    public function getDateCreated()
    {
        return $this->date_created;
    }

    public function setDateCreated(\DateTimeInterface $createdDate)
    {
        $this->date_created = $createdDate;

        return $this;
    }

    public function getDateUpd()
    {
        return $this->date_upd;
    }

    public function setDateUpd(\DateTimeInterface $date_upd)
    {
        $this->date_upd = $date_upd;

        return $this;
    }


    public function getAuthor()
    {
        return $this->author;
    }

    public function setAuthor($author)
    {
        $this->author = $author;

        return $this;
    }

    public function getPicture()
    {
        return $this->picture;
    }

    public function setPicture($picture)
    {
        $this->picture = $picture;

        return $this;
    }

    public function getActive()
    {
        return $this->active;
    }

    public function setActive($active)
    {
        $this->active = $active;

        return $this;
    }


    /**
     * @return mixed
     */
    public function getNew()
    {
        return $this->new;
    }

    /**
     * @param mixed $new
     */
    public function setNew($new)
    {
        $this->new = $new;
    }


    /**
     * @return int
     */
    public function getViews()
    {
        return $this->views;
    }

    /**
     * @param int $views
     */
    public function setViews($views)
    {
        $this->views = $views;
    }

    /**
     * @return int
     */
    public function getLike()
    {
        return $this->like;
    }

    /**
     * @param int $like
     */
    public function setLike($like)
    {
        $this->like = $like;
    }




    /**
     * @return mixed
     */
    public function getPhotoPath()
    {
        return $this->photoPath;
    }

    /**
     * @param mixed $photoPath
     */
    public function setPhotoPath($photoPath)
    {
        $this->photoPath = $photoPath;
    }

    public function subirFoto()
    {
        if (null === $this->photoPath) {
            return;
        }
        $nombreArchivoFoto = uniqid('post-').$this->id.'.'.$this->photoPath->getClientOriginalExtension();
        $this->photoPath->move($_SERVER['DOCUMENT_ROOT'].'/img/post/', $nombreArchivoFoto);
        //$path=$_SERVER['DOCUMENT_ROOT'].'/img/post/'.$nombreArchivoFoto;
        //$pic = new Loginrss('amazon');
        //$a=$pic->uploadPicAmz($path);
        $a = 'https://'.self::MEDIA_SERVER.'/img/post/'.$nombreArchivoFoto;
        $this->setPicture($a);

    }
}

<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\PersonRepository")
 */
class Person
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $name;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $lastname;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $description;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $token;

    /**
     * @ORM\Column(type="datetime")
     */
    private $createdDate;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $activatedDate;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $picture;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $photoPath;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $ipClient;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $urlValidate;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $country;


    public function getId()
    {
        return $this->id;
    }

    public function getName()
    {
        return $this->name;
    }

    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    public function getLastname()
    {
        return $this->lastname;
    }

    public function setLastname($lastname)
    {
        $this->lastname = $lastname;

        return $this;
    }

    public function getDescription()
    {
        return $this->description;
    }

    public function setDescription( $description)
    {
        $this->description = $description;

        return $this;
    }

    public function getToken()
    {
        return $this->token;
    }

    public function setToken($token)
    {
        $this->token = $token;

        return $this;
    }

    public function getCreatedDate()
    {
        return $this->createdDate;
    }

    public function setCreatedDate(\DateTimeInterface $createdDate)
    {
        $this->createdDate = $createdDate;

        return $this;
    }

    public function getActivatedDate()
    {
        return $this->activatedDate;
    }

    public function setActivatedDate(\DateTimeInterface $activatedDate)
    {
        $this->activatedDate = $activatedDate;

        return $this;
    }

    public function getPicture()
    {
        return $this->picture;
    }

    public function setPicture( $picture)
    {
        $this->picture = $picture;

        return $this;
    }

    public function getPhotoPath()
    {
        return $this->photoPath;
    }

    public function setPhotoPath($photoPath)
    {
        $this->photoPath = $photoPath;

        return $this;
    }

    public function getIpClient()
    {
        return $this->ipClient;
    }

    public function setIpClient($ipClient)
    {
        $this->ipClient = $ipClient;

        return $this;
    }

    public function getUrlValidate()
    {
        return $this->urlValidate;
    }

    public function setUrlValidate($urlValidate)
    {
        $this->urlValidate = $urlValidate;

        return $this;
    }

    public function getCountry()
    {
        return $this->country;
    }

    public function setCountry($country)
    {
        $this->country = $country;

        return $this;
    }

}

<?php

namespace App\Entity;

use App\Entity\User as user;
use App\Entity\Person as person;

class Register
{
    private $user;
    private $person;

    public function __construct()
    {
        $this->user= new user();
        $this->person= new person();
        $this->person->setCreatedDate(new \DateTime());
        $this->person->setActivatedDate(new \DateTime());


        /*for user*/
        $this->user->setActive(false);
        $this->user->setExpired(true);
        $this->user->setRoles('ROLE_USER');
    }

    /**
     * @return mixed
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * @param mixed $user
     */
    public function setUser($user)
    {
        $this->user = $user;
    }

    /**
     * @return mixed
     */
    public function getPerson()
    {
        return $this->person;
    }

    /**
     * @param mixed $person
     */
    public function setPerson($person)
    {
        $this->person = $person;
    }
}

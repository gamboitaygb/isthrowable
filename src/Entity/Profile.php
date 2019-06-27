<?php

namespace App\Entity;

use App\Entity\User as user;
use App\Entity\Person as person;

class Profile
{

    private $User;

    private $Person;


    public function __construct(user $user,person $person)
    {
        $this->User=$user;
        $this->Person=$person;
    }

    public function getUser()
    {
        return $this->User;
    }

    public function setUser($User)
    {
        $this->User = $User;

        return $this;
    }

    public function getPerson()
    {
        return $this->Person;
    }

    public function setPerson($Person)
    {
        $this->Person = $Person;

        return $this;
    }
}

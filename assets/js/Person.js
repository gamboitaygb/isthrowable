'use strict';

class Person {

    constructor(){
        this.name;
        this.lastName;
        this.age;
        this.address;
    }

    setName(name)
    {

        this.name=name;
    }
    getName(){
        this.name;
    }
    setLastName(lastname)
    {
        this.lastName=lastname;
    }
    getLastName()
    {
        this.lastName;
    }
    setAge(age)
    {
        this.age=age;
    }
    getAge()
    {
        this.age;
    }

    setAdress(address)
    {
        this.address=address;
    }

    getAddress()
    {
        this.address;
    }
}

module.exports = Person;
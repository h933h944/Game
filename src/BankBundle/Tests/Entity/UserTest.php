<?php
namespace BankBundle\Tests\Entity;

use BankBundle\Entity\User;

class UserTest extends \PHPUnit_Framework_TestCase
{
    public function testUser()
    {
        $user = new User('12345', '54321');

        $this->assertEquals(null, $user->getId());

        $this->assertEquals('54321', $user->getPassword());

        $this->assertEquals('12345', $user->getAccount());

        $this->assertEquals(0, $user->getBalance());


        $user->setAccount('qwertasdf');
        $this->assertEquals('qwertasdf', $user->getAccount());

        $user->setPassword('rewqfdsa');
        $this->assertEquals('rewqfdsa', $user->getPassword());

        $user->setBalance(12345);
        $this->assertEquals(12345, $user->getBalance());
    }
}

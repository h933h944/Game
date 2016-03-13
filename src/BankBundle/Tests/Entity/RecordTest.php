<?php
namespace BankBundle\Tests\Entity;

use BankBundle\Entity\Record;
use BankBundle\Entity\User;

class RecordTest extends \PHPUnit_Framework_TestCase
{
    public function testRecord()
    {
        $user = new User('account', 'password');

        $beforeTime = new \DateTime('now');
        $record = new Record($user, 0, 0);
        $afterTime = new \DateTime('now');

        $this->assertEquals(null, $record->getId());

        $this->assertEquals(0, $record->getBalance());

        $this->assertEquals(0, $record->getAmount());

        $this->assertEquals($user, $record->getUser());

        $this->assertEquals(new \DateTime(), $record->getCreateTime());
        $this->assertGreaterThanOrEqual($beforeTime, $record->getCreateTime());
        $this->assertLessThanOrEqual($afterTime, $record->getCreateTime());


        $record->setBalance(1);
        $this->assertEquals(1, $record->getBalance());

        $record->setAmount(2);
        $this->assertEquals(2, $record->getAmount());

        $newUser = new User('account', 'password');

        $record->setUser($newUser);
        $this->assertEquals($newUser, $record->getUser());

        $recordArray = $record->toArray();
        $this->assertEquals(null, $recordArray['id']);
        $this->assertEquals(1, $recordArray['balance']);
        $this->assertEquals(2, $recordArray['amount']);
        $this->assertEquals($newUser, $recordArray['user']);

        $createTimeFormat = $record->getCreateTime()->Format('Y-m-d H:i:s');

        $this->assertEquals($createTimeFormat, $recordArray['createTime']);
    }
}
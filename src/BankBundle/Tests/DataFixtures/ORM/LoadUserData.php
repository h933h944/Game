<?php
namespace BankBundle\Tests\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use BankBundle\Entity\User;

class LoadUserData extends AbstractFixture implements FixtureInterface
{
    public function load(ObjectManager $manager)
    {
        $user = new User('test', 'test', 10000);
        $this->setReference('user', $user);

        $manager->persist($user);
        $manager->flush();
    }
}

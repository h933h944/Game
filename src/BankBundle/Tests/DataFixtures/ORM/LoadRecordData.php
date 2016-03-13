<?php
namespace BankBundle\Tests\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use BankBundle\Entity\Record;

class LoadRecordData extends AbstractFixture implements FixtureInterface
{
    public function load(ObjectManager $manager)
    {
        $user = $manager->getRepository('BankBundle\Entity\User')->find(1);

        $count = 1;

        while ($count <= 3) {
            $record = new Record($user, 10000, 10000);
            $manager->persist($record);
            $manager->flush();

            $count = $count + 1;
        }
    }
}

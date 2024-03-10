<?php

namespace App\DataFixtures;

use App\Entity\Log;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class AppFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $log = new Log();
        $log->setServiceName('USERS-SERVICE');
        $log->setMethod('POST');
        $log->setEndpoint('/user');
        $log->setStatusCode(201);
        $log->setCreated(new \DateTimeImmutable('now'));

        $manager->persist($log);

        $log = new Log();
        $log->setServiceName('INVOICE-SERVICE');
        $log->setMethod('GET');
        $log->setEndpoint('/invoice/1');
        $log->setStatusCode(200);
        $log->setCreated(new \DateTimeImmutable('now'));

        $manager->persist($log);

        $manager->flush();
    }
}

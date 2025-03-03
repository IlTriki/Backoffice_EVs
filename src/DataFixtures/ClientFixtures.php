<?php

namespace App\DataFixtures;

use App\Entity\Client;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class ClientFixtures extends Fixture
{
    public function load(ObjectManager $manager)
    {
        $clients = [
            [
                'firstname' => 'John',
                'lastname' => 'Doe',
                'email' => 'john.doe@example.com',
                'phoneNumber' => '0123456789',
                'address' => '123 Main St'
            ],
            [
                'firstname' => 'Jane',
                'lastname' => 'Smith',
                'email' => 'jane.smith@example.com',
                'phoneNumber' => '0987654321',
                'address' => '456 Oak Ave'
            ],
        ];

        foreach ($clients as $clientData) {
            $client = new Client();
            $client->setFirstname($clientData['firstname']);
            $client->setLastname($clientData['lastname']);
            $client->setEmail($clientData['email']);
            $client->setPhoneNumber($clientData['phoneNumber']);
            $client->setAddress($clientData['address']);
            
            $manager->persist($client);
        }

        $manager->flush();
    }
} 
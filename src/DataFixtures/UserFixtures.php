<?php

namespace App\DataFixtures;

use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserFixtures extends Fixture
{
    private UserPasswordHasherInterface $passwordHasher;

    public function __construct(UserPasswordHasherInterface $passwordHasher)
    {
        $this->passwordHasher = $passwordHasher;
    }

    public function load(ObjectManager $manager)
    {
        $admin = new User();
        $admin->setEmail('mustang@amestris-military.com');
        $admin->setFirstname('Roy');
        $admin->setLastname('Mustang');
        $admin->setRoles(['ROLE_ADMIN']);
        $admin->setPassword($this->passwordHasher->hashPassword($admin, 'admin123'));
        $manager->persist($admin);
        
        $user1 = new User();
        $user1->setEmail('fullmetal@state-alchemist.org');
        $user1->setFirstname('Edward');
        $user1->setLastname('Elric');
        $user1->setRoles(['ROLE_USER']);
        $user1->setPassword($this->passwordHasher->hashPassword($user1, 'user123'));
        $manager->persist($user1);
        
        $user2 = new User();
        $user2->setEmail('alphonse@resembool.net');
        $user2->setFirstname('Alphonse');
        $user2->setLastname('Elric');
        $user2->setRoles(['ROLE_USER']);
        $user2->setPassword($this->passwordHasher->hashPassword($user2, 'user123'));
        $manager->persist($user2);
        
        $manager1 = new User();
        $manager1->setEmail('fuhrer@central-command.gov');
        $manager1->setFirstname('King');
        $manager1->setLastname('Bradley');
        $manager1->setRoles(['ROLE_MANAGER']);
        $manager1->setPassword($this->passwordHasher->hashPassword($manager1, 'manager123'));
        $manager->persist($manager1);

        $manager->flush();
    }
}

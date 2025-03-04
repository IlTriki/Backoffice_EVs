<?php

namespace App\Tests\Entity;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserCreationTest extends TestCase
{
    public function testCreateUser(): void
    {
        $user = new User();
        $user->setEmail('test@example.com');
        $user->setFirstname('Test');
        $user->setLastname('User');
        $user->setRoles(['ROLE_USER']);
        
        /** @var UserPasswordHasherInterface&\PHPUnit\Framework\MockObject\MockObject $passwordHasher */
        $passwordHasher = $this->createMock(UserPasswordHasherInterface::class);
        $passwordHasher->method('hashPassword')->willReturn('hashed_password');
        
        /** @var EntityManagerInterface&\PHPUnit\Framework\MockObject\MockObject $entityManager */
        $entityManager = $this->createMock(EntityManagerInterface::class);
        
        $hashedPassword = $passwordHasher->hashPassword($user, 'password123');
        $user->setPassword($hashedPassword);
        
        $entityManager->persist($user);
        $entityManager->flush();
        
        $this->assertEquals('test@example.com', $user->getEmail());
        $this->assertEquals('hashed_password', $user->getPassword());
        $this->assertEquals('Test', $user->getFirstname());
        $this->assertEquals('User', $user->getLastname());
        $this->assertContains('ROLE_USER', $user->getRoles());
    }
}

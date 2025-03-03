<?php

namespace App\Command;

use App\Entity\Client;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[AsCommand(
    name: 'app:create-client',
    description: 'Creates a new client',
)]
class CreateClientCommand extends Command
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private ValidatorInterface $validator
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $client = new Client();
        
        $firstname = $io->ask('First name');
        $lastname = $io->ask('Last name');
        $email = $io->ask('Email');
        $phoneNumber = $io->ask('Phone number');
        $address = $io->ask('Address');

        $client->setFirstname($firstname);
        $client->setLastname($lastname);
        $client->setEmail($email);
        $client->setPhoneNumber($phoneNumber);
        $client->setAddress($address);

        $errors = $this->validator->validate($client);
        if (count($errors) > 0) {
            foreach ($errors as $error) {
                $io->error($error->getMessage());
            }
            return Command::FAILURE;
        }

        try {
            $this->entityManager->persist($client);
            $this->entityManager->flush();
            
            $io->success('Client created successfully');
            return Command::SUCCESS;
        } catch (\Exception $e) {
            $io->error('An error occurred: ' . $e->getMessage());
            return Command::FAILURE;
        }
    }
} 
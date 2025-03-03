<?php

namespace App\Command;

use App\Entity\Product;
use Doctrine\ORM\EntityManagerInterface;
use League\Csv\Reader;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:import-products',
    description: 'Import products from CSV file',
)]
class ImportProductsCommand extends Command
{
    public function __construct(
        private EntityManagerInterface $entityManager
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('file', InputArgument::REQUIRED, 'CSV file path')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $filePath = $input->getArgument('file');

        if (!file_exists($filePath)) {
            $io->error('File not found');
            return Command::FAILURE;
        }

        try {
            $csv = Reader::createFromPath($filePath, 'r');
            $csv->setHeaderOffset(0);
            $csv->setEscape('');

            $headers = $csv->getHeader();
            $expectedHeaders = ['name', 'description', 'price'];
            if ($headers !== $expectedHeaders) {
                $io->error('Invalid CSV format. Expected headers: ' . implode(', ', $expectedHeaders));
                return Command::FAILURE;
            }

            $count = 0;
            foreach ($csv as $record) {
                $product = new Product();
                $product->setName($record['name']);
                $product->setDescription($record['description']);
                $product->setPrice((float) $record['price']);

                $this->entityManager->persist($product);
                $count++;

                if ($count % 100 === 0) {
                    $this->entityManager->flush();
                    $this->entityManager->clear();
                }
            }

            $this->entityManager->flush();

            $io->success(sprintf('%d products imported successfully', $count));
            return Command::SUCCESS;

        } catch (\Exception $e) {
            $io->error('An error occurred during import: ' . $e->getMessage());
            return Command::FAILURE;
        }
    }
} 
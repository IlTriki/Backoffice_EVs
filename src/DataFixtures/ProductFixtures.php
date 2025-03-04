<?php

namespace App\DataFixtures;

use App\Entity\Product;
use App\Enum\Brand;
use App\Enum\VehicleType;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\String\Slugger\SluggerInterface;

class ProductFixtures extends Fixture
{
    private $slugger;
    
    public function __construct(SluggerInterface $slugger)
    {
        $this->slugger = $slugger;
    }
    
    public function load(ObjectManager $manager): void
    {
        $products = [
            ['name' => 'Ioniq 5', 'brand' => 'Hyundai', 'model' => 'Ioniq 5', 'type' => 'Electric', 'year' => 2024, 'description' => 'The Hyundai Ioniq 5 is a futuristic electric SUV with a spacious interior, fast charging capabilities, and an impressive range of up to 315 miles. It features advanced driver assistance systems and a unique retro-modern design.', 'price' => 49800],
            ['name' => 'Kia EV3', 'brand' => 'Kia', 'model' => 'EV3', 'type' => 'Electric', 'year' => 2025, 'description' => "The Kia EV3 is an upcoming compact electric SUV expected to offer high efficiency, modern technology, and Kia's signature design language. It will likely feature a long range and fast charging.", 'price' => 48000],
            ['name' => 'Polestar 2', 'brand' => 'Polestar', 'model' => '2', 'type' => 'Electric', 'year' => 2024, 'description' => 'The Polestar 2 is a high-performance electric fastback featuring a minimalist interior, Google-based infotainment, and a range of up to 320 miles. It delivers a premium EV driving experience with sporty dynamics.', 'price' => 20900],
            ['name' => 'Polestar 3', 'brand' => 'Polestar', 'model' => '3', 'type' => 'Electric', 'year' => 2024, 'description' => 'The Polestar 3 is a luxury electric SUV designed for long-distance travel with a powerful dual-motor setup, high-end materials, and an expected range of over 300 miles. It integrates advanced driver-assistance features.', 'price' => 41900],
            ['name' => 'Range Rover Velar', 'brand' => 'Land Rover', 'model' => 'Velar', 'type' => 'Hybrid', 'year' => 2024, 'description' => 'The Range Rover Velar is a stylish luxury SUV available with a plug-in hybrid (PHEV) powertrain. It offers a sleek design, high-end interior, and off-road capabilities with a focus on comfort and technology.', 'price' => 61500],
            ['name' => 'Volvo EX30', 'brand' => 'Volvo', 'model' => 'EX30', 'type' => 'Electric', 'year' => 2024, 'description' => 'The Volvo EX30 is a compact electric SUV featuring Scandinavian design, a minimalist interior, and a focus on sustainability. It offers impressive range and advanced safety features at an accessible price point.', 'price' => 36000],
        ];

        foreach ($products as $productData) {
            $product = new Product();
            $product->setName($productData['name']);
            $product->setBrand(Brand::from($productData['brand']));
            $product->setModel($productData['model']);
            $product->setType(VehicleType::from($productData['type']));
            $product->setYear($productData['year']);
            $product->setDescription($productData['description']);
            $product->setPrice($productData['price']);
            
            // Generate image filename based on product name
            $safeFilename = $this->slugger->slug(strtolower($productData['name']));
            $imageFilename = $safeFilename . '.png';
            $product->setImageFilename($imageFilename);

            $manager->persist($product);
        }

        $manager->flush();
    }
}
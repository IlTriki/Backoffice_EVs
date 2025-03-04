<?php

namespace App\Tests\Service;

use App\Entity\Product;
use App\Repository\ProductRepository;
use App\Service\ProductCsvExporter;
use PHPUnit\Framework\TestCase;

class ProductCsvExporterTest extends TestCase
{
    public function testExportToCsv(): void
    {
        $product1 = $this->createMockProduct('Ioniq 5', 'Electric car UwU', 49800);
        $product2 = $this->createMockProduct('Kia EV3', 'Another electric car HIHU', 48000);
        
        /** @var ProductRepository&\PHPUnit\Framework\MockObject\MockObject $productRepository */
        $productRepository = $this->createMock(ProductRepository::class);
        $productRepository->expects($this->once())
            ->method('findAll')
            ->willReturn([$product1, $product2]);
        
        $exporter = new ProductCsvExporter($productRepository);
        
        $csvContent = $exporter->exportToCsv();
        
        $this->assertIsString($csvContent);
        $this->assertStringContainsString('name,description,price', $csvContent);
        $this->assertStringContainsString('"Ioniq 5","Electric car UwU",49800', $csvContent);
        $this->assertStringContainsString('"Kia EV3","Another electric car HIHU",48000', $csvContent);
    }

    private function createMockProduct(string $name, string $description, float $price): \PHPUnit\Framework\MockObject\MockObject
    {
        $product = $this->createMock(Product::class);
        
        $product->method('getName')->willReturn($name);
        $product->method('getDescription')->willReturn($description);
        $product->method('getPrice')->willReturn($price);
        
        return $product;
    }
} 
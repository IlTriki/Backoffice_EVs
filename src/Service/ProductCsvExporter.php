<?php

namespace App\Service;

use App\Repository\ProductRepository;
use League\Csv\Writer;
use SplTempFileObject;

class ProductCsvExporter
{
    public function __construct(private ProductRepository $productRepository)
    {
    }

    public function exportToCsv(): string
    {
        $csv = Writer::createFromFileObject(new SplTempFileObject());
        $csv->setEscape('');
        
        $csv->insertOne(['name', 'description', 'price']);
        
        $products = $this->productRepository->findAll();
        $records = array_map(function($product) {
            return [
                $product->getName(),
                $product->getDescription(),
                $product->getPrice(),
            ];
        }, $products);
        
        $csv->insertAll($records);
        
        return $csv->toString();
    }
}

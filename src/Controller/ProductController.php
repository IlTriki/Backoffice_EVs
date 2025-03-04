<?php

namespace App\Controller;

use App\Entity\Product;
use App\Form\ProductType;
use App\Repository\ProductRepository;
use App\Service\ProductCsvExporter;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\HeaderUtils;
use Symfony\Component\String\Slugger\SluggerInterface;
use Psr\Log\LoggerInterface;

#[Route('/products')]
class ProductController extends AbstractController
{
    private $logger;

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    #[Route('/', name: 'product_index')]
    public function index(ProductRepository $productRepository): Response
    {
        $this->denyAccessUnlessGranted('view', new Product());
        
        $products = $productRepository->findAllOrderedByPriceDesc();
        
        return $this->render('product/index.html.twig', [
            'products' => $products
        ]);
    }

    #[Route('/new', name: 'product_new')]
    public function new(Request $request, EntityManagerInterface $entityManager, SluggerInterface $slugger): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');
        
        $product = new Product();
        $form = $this->createForm(ProductType::class, $product);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $imageFile = $form->get('imageFile')->getData();
            if ($imageFile) {
                $this->logger->info('Image file uploaded for new product: ' . $product->getName());
                $newFilename = $this->handleImageUpload($imageFile, $product, $slugger);
                $product->setImageFilename($newFilename);
                $this->addFlash('success', 'Product created successfully with image');
            } else {
                $this->logger->warning('No image file uploaded for new product: ' . $product->getName());
                $this->addFlash('success', 'Product created successfully without image');
            }
            
            $entityManager->persist($product);
            $entityManager->flush();

            return $this->redirectToRoute('product_index');
        }

        return $this->render('product/form.html.twig', [
            'form' => $form->createView(),
            'title' => 'New Product',
        ]);
    }

    #[Route('/{id}/edit', name: 'product_edit')]
    public function edit(Request $request, Product $product, EntityManagerInterface $entityManager, SluggerInterface $slugger): Response
    {
        $this->denyAccessUnlessGranted('edit', $product);
        
        $form = $this->createForm(ProductType::class, $product);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $imageFile = $form->get('imageFile')->getData();
            if ($imageFile) {
                $this->logger->info('Image file uploaded for product edit: ' . $product->getName());
                $newFilename = $this->handleImageUpload($imageFile, $product, $slugger);
                $product->setImageFilename($newFilename);
                $this->addFlash('success', 'Product updated successfully with new image');
            } else {
                $this->logger->warning('No image file uploaded for product edit: ' . $product->getName());
                $this->addFlash('success', 'Product updated successfully without changing image');
            }
            
            $entityManager->flush();

            return $this->redirectToRoute('product_index');
        }

        return $this->render('product/form.html.twig', [
            'form' => $form->createView(),
            'title' => 'Edit Product',
        ]);
    }

    private function handleImageUpload($imageFile, Product $product, SluggerInterface $slugger): string
    {
        try {
            $productName = $product->getName();
            $safeFilename = $slugger->slug(strtolower($productName));
            $newFilename = $safeFilename . '-' . uniqid() . '.png';
            $targetDirectory = $this->getParameter('kernel.project_dir') . '/public/produits';
            
            $this->logger->info('Processing image upload:', [
                'original_name' => $imageFile->getClientOriginalName(),
                'mime_type' => $imageFile->getMimeType(),
                'size' => $imageFile->getSize(),
                'new_filename' => $newFilename,
                'target_directory' => $targetDirectory
            ]);
            
            if (!file_exists($targetDirectory)) {
                mkdir($targetDirectory, 0777, true);
                $this->logger->info('Created directory: ' . $targetDirectory);
            }

            $imageFile->move($targetDirectory, $newFilename);
            $this->logger->info('Image successfully moved to: ' . $targetDirectory . '/' . $newFilename);
            
            return $newFilename;
        } catch (\Exception $e) {
            $this->logger->error('Error uploading image: ' . $e->getMessage(), [
                'exception' => $e,
                'product' => $product->getName()
            ]);
            $this->addFlash('error', 'There was a problem uploading the image: ' . $e->getMessage());
            return '';
        }
    }

    #[Route('/{id}/delete', name: 'product_delete')]
    public function delete(Product $product, EntityManagerInterface $entityManager): Response
    {
        $this->denyAccessUnlessGranted('delete', $product);
        
        // Delete the image file if it exists
        if ($product->getImageFilename()) {
            $imagePath = $this->getParameter('kernel.project_dir') . '/public/produits/' . $product->getImageFilename();
            if (file_exists($imagePath)) {
                unlink($imagePath);
                $this->logger->info('Deleted image file: ' . $imagePath);
            }
        }
        
        $entityManager->remove($product);
        $entityManager->flush();

        $this->addFlash('success', 'Product deleted successfully');
        return $this->redirectToRoute('product_index');
    }

    #[Route('/export', name: 'product_export')]
    public function export(ProductCsvExporter $exporter): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');
        
        $csv = $exporter->exportToCsv();
        
        $response = new Response($csv);
        $disposition = HeaderUtils::makeDisposition(
            HeaderUtils::DISPOSITION_ATTACHMENT,
            'products.csv'
        );
        
        $response->headers->set('Content-Type', 'text/csv');
        $response->headers->set('Content-Disposition', $disposition);
        
        return $response;
    }
}

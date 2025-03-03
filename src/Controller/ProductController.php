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

#[Route('/products')]
class ProductController extends AbstractController
{
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
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');
        
        $product = new Product();
        $form = $this->createForm(ProductType::class, $product);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($product);
            $entityManager->flush();

            $this->addFlash('success', 'Product created successfully');
            return $this->redirectToRoute('product_index');
        }

        return $this->render('product/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}/edit', name: 'product_edit')]
    public function edit(Request $request, Product $product, EntityManagerInterface $entityManager): Response
    {
        $this->denyAccessUnlessGranted('edit', $product);
        
        $form = $this->createForm(ProductType::class, $product);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            $this->addFlash('success', 'Product updated successfully');
            return $this->redirectToRoute('product_index');
        }

        return $this->render('product/edit.html.twig', [
            'product' => $product,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}/delete', name: 'product_delete')]
    public function delete(Product $product, EntityManagerInterface $entityManager): Response
    {
        $this->denyAccessUnlessGranted('delete', $product);
        
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

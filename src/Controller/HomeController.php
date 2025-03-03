<?php

namespace App\Controller;

use App\Repository\ProductRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class HomeController extends AbstractController
{
    #[Route('/home', name: 'home')]
    public function index(ProductRepository $productRepository): Response
    {
        $products = $productRepository->findBy([], ['price' => 'DESC'], 5);
        
        return $this->render('home/index.html.twig', [
            'products' => $products
        ]);
    }

    #[Route('/', name: 'base')]
    public function base(): Response
    {
        return $this->redirectToRoute('home');
    }
}

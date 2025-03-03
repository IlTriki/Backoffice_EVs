<?php

namespace App\Controller;

use App\Entity\Client;
use App\Form\ClientType;
use App\Repository\ClientRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/clients')]
class ClientController extends AbstractController
{
    #[Route('/', name: 'client_index')]
    public function index(ClientRepository $clientRepository): Response
    {
        $this->denyAccessUnlessGranted('view', new Client());
        
        return $this->render('client/index.html.twig', [
            'clients' => $clientRepository->findAll()
        ]);
    }

    #[Route('/new', name: 'client_new')]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $this->denyAccessUnlessGranted('edit', new Client());
        
        $client = new Client();
        $form = $this->createForm(ClientType::class, $client);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($client);
            $entityManager->flush();

            $this->addFlash('success', 'Client created successfully');
            return $this->redirectToRoute('client_index');
        }

        return $this->render('client/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}/edit', name: 'client_edit')]
    public function edit(Request $request, Client $client, EntityManagerInterface $entityManager): Response
    {
        $this->denyAccessUnlessGranted('edit', $client);
        
        $form = $this->createForm(ClientType::class, $client);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            $this->addFlash('success', 'Client updated successfully');
            return $this->redirectToRoute('client_index');
        }

        return $this->render('client/edit.html.twig', [
            'client' => $client,
            'form' => $form->createView(),
        ]);
    }
} 
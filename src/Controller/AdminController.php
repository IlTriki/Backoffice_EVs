<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\UserType;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

#[Route('/admin')]
#[IsGranted('ROLE_ADMIN')]
class AdminController extends AbstractController
{
    #[Route('/users', name: 'admin_users')]
    public function users(UserRepository $userRepository): Response
    {
        return $this->render('admin/users.html.twig', [
            'users' => $userRepository->findAll()
        ]);
    }

    #[Route('/user/new', name: 'admin_user_new')]
    public function newUser(Request $request, EntityManagerInterface $entityManager, UserRepository $userRepository, UserPasswordHasherInterface $passwordHasher): Response
    {
        $user = new User();
        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $existingUser = $userRepository->findOneBy(['email' => $user->getEmail()]);
            
            if ($existingUser) {
                $this->addFlash('error', 'A user with this email already exists.');
                return $this->render('admin/user_form.html.twig', [
                    'form' => $form->createView(),
                    'title' => 'New User'
                ]);
            }

            try {
                $randomPassword = bin2hex(random_bytes(8));
                $user->setPassword(
                    $passwordHasher->hashPassword($user, $randomPassword)
                );

                $entityManager->persist($user);
                $entityManager->flush();

                $this->addFlash('success', sprintf(
                    'User created successfully. Initial password: %s',
                    $randomPassword
                ));
                
                return $this->redirectToRoute('admin_users');
            } catch (\Exception $e) {
                $this->addFlash('error', 'An error occurred while creating the user. ' . $e->getMessage());
                return $this->render('admin/user_form.html.twig', [
                    'form' => $form->createView(),
                    'title' => 'New User'
                ]);
            }
        }

        return $this->render('admin/user_form.html.twig', [
            'form' => $form->createView(),
            'title' => 'New User'
        ]);
    }

    #[Route('/user/{id}/edit', name: 'admin_user_edit')]
    public function editUser(User $user, Request $request, EntityManagerInterface $entityManager): Response
    {
        // Check if trying to edit another admin
        if (in_array('ROLE_ADMIN', $user->getRoles())) {
            throw new AccessDeniedException('You cannot edit other admin accounts.');
        }

        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            $this->addFlash('success', 'User updated successfully');
            return $this->redirectToRoute('admin_users');
        }

        return $this->render('admin/user_form.html.twig', [
            'form' => $form,
            'title' => 'Edit User'
        ]);
    }

    #[Route('/user/{id}/delete', name: 'admin_user_delete')]
    public function deleteUser(User $user, EntityManagerInterface $entityManager): Response
    {
        // Prevent deletion of admin accounts except your own
        if (in_array('ROLE_ADMIN', $user->getRoles())) {
            $this->addFlash('error', 'You cannot delete admin accounts.');
            return $this->redirectToRoute('admin_users');
        }
        
        $entityManager->remove($user);
        $entityManager->flush();
        
        $this->addFlash('success', 'User deleted successfully');
        return $this->redirectToRoute('admin_users');
    }
}

<?php

namespace App\Controller;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class SecurityController extends AbstractController
{
    #[Route('/login', name: 'app_login')]
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        if ($this->getUser()) {
            return $this->redirectToRoute('home');
        }

        $error = $authenticationUtils->getLastAuthenticationError();
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('security/login.html.twig', [
            'last_username' => $lastUsername,
            'error' => $error,
        ]);
    }

    #[Route('/register', name: 'app_register')]
    public function register(
        Request $request,
        UserPasswordHasherInterface $userPasswordHasher,
        EntityManagerInterface $entityManager
    ): Response {
        if ($this->getUser()) {
            return $this->redirectToRoute('home');
        }

        if ($request->isMethod('POST')) {
            if (!$this->isCsrfTokenValid('register', $request->request->get('_csrf_token'))) {
                $this->addFlash('error', 'Invalid CSRF token');
                return $this->redirectToRoute('app_register');
            }

            try {
                $email = $request->request->get('email');
                $existingUser = $entityManager->getRepository(User::class)->findOneBy(['email' => $email]);
                
                if ($existingUser) {
                    $this->addFlash('error', 'Email already exists');
                    return $this->redirectToRoute('app_register');
                }

                $user = new User();
                $user->setEmail($email)
                    ->setFirstname($request->request->get('firstname'))
                    ->setLastname($request->request->get('lastname'))
                    ->setRoles(['ROLE_USER'])
                    ->setPassword(
                        $userPasswordHasher->hashPassword(
                            $user,
                            $request->request->get('password')
                        )
                    );

                $entityManager->persist($user);
                $entityManager->flush();

                $this->addFlash('success', 'Account created successfully!');
                return $this->redirectToRoute('app_login');
            } catch (\Exception $e) {
                $this->addFlash('error', $e->getMessage());
            }
        }

        return $this->render('registration/register.html.twig');
    }

    #[Route('/logout', name: 'app_logout')]
    public function logout(): void
    {
        throw new \LogicException('This method can be blank - it will be intercepted by the logout key on your firewall.');
    }
}

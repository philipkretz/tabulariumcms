<?php
namespace App\Controller;

use App\Entity\User;
use App\Entity\UserProfile;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\{Request, Response};
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class AuthController extends AbstractController
{
    #[Route("/register", name: "app_register")]
    public function register(
        Request $request,
        UserPasswordHasherInterface $passwordHasher,
        EntityManagerInterface $em
    ): Response {
        if ($this->getUser()) {
            return $this->redirectToRoute("user_profile");
        }

        if ($request->isMethod("POST")) {
            $email = $request->request->get("email");
            $password = $request->request->get("password");
            $firstName = $request->request->get("firstName");
            $lastName = $request->request->get("lastName");

            // Validate
            if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $this->addFlash("error", "Valid email is required");
                return $this->render("auth/register.html.twig", ["data" => $request->request->all()]);
            }

            if (strlen($password) < 8) {
                $this->addFlash("error", "Password must be at least 8 characters");
                return $this->render("auth/register.html.twig", ["data" => $request->request->all()]);
            }

            // Check if user exists
            if ($em->getRepository(User::class)->findOneBy(["email" => $email])) {
                $this->addFlash("error", "Email already registered");
                return $this->render("auth/register.html.twig", ["data" => $request->request->all()]);
            }

            // Create user
            $user = new User();
            $user->setEmail($email);
            $user->setPassword($passwordHasher->hashPassword($user, $password));
            $user->setRoles(["ROLE_USER"]);
            
            $em->persist($user);
            $em->flush();

            // Create profile
            $profile = new UserProfile();
            $profile->setUser($user);
            $profile->setBiography($firstName && $lastName ? "{$firstName} {$lastName}" : "");
            $em->persist($profile);
            $em->flush();

            $this->addFlash("success", "Registration successful! Please login.");
            return $this->redirectToRoute("app_login");
        }

        return $this->render("auth/register.html.twig");
    }

    #[Route("/login", name: "app_login")]
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        if ($this->getUser()) {
            return $this->redirectToRoute("user_profile");
        }

        $error = $authenticationUtils->getLastAuthenticationError();
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render("auth/login.html.twig", [
            "last_username" => $lastUsername,
            "error" => $error,
        ]);
    }

    #[Route("/logout", name: "app_logout")]
    public function logout(): void
    {
        // Symfony handles this automatically
    }
}
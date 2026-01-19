<?php
namespace App\Controller;

use App\Entity\{User, Seller};
use App\Repository\SiteSettingsRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\{Request, Response};
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class SellerController extends AbstractController
{
    #[Route("/seller/register", name: "seller_register")]
    public function register(
        Request $request,
        UserPasswordHasherInterface $passwordHasher,
        EntityManagerInterface $em,
        SiteSettingsRepository $settingsRepository
    ): Response {
        // Check if seller registration is enabled
        $enabled = $settingsRepository->findOneBy(["settingKey" => "enable_seller_registration"]);
        if (!$enabled || !$enabled->getValue()) {
            throw $this->createNotFoundException("Seller registration is currently disabled");
        }

        if ($this->getUser()) {
            return $this->redirectToRoute("seller_dashboard");
        }

        if ($request->isMethod("POST")) {
            $email = $request->request->get("email");
            $password = $request->request->get("password");
            $companyName = $request->request->get("companyName");
            $contactPerson = $request->request->get("contactPerson");
            $phone = $request->request->get("phone");
            $taxNumber = $request->request->get("taxNumber");

            // Validate
            if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $this->addFlash("error", "Valid email is required");
                return $this->render("seller/register.html.twig", ["data" => $request->request->all()]);
            }

            if (strlen($password) < 8) {
                $this->addFlash("error", "Password must be at least 8 characters");
                return $this->render("seller/register.html.twig", ["data" => $request->request->all()]);
            }

            if (empty($companyName)) {
                $this->addFlash("error", "Company name is required");
                return $this->render("seller/register.html.twig", ["data" => $request->request->all()]);
            }

            // Check if user exists
            if ($em->getRepository(User::class)->findOneBy(["email" => $email])) {
                $this->addFlash("error", "Email already registered");
                return $this->render("seller/register.html.twig", ["data" => $request->request->all()]);
            }

            // Create user with SELLER role
            $user = new User();
            $user->setEmail($email);
            $user->setPassword($passwordHasher->hashPassword($user, $password));
            $user->setRoles(["ROLE_USER", "ROLE_SELLER"]);
            
            $em->persist($user);
            $em->flush();

            // Create seller profile
            $seller = new Seller();
            $seller->setUser($user);
            $seller->setCompanyName($companyName);
            $seller->setContactPerson($contactPerson);
            $seller->setPhone($phone);
            $seller->setEmail($email);
            $seller->setTaxNumber($taxNumber);
            $seller->setIsApproved(false); // Requires admin approval
            $seller->setIsActive(false);
            $seller->setCommissionRate("15.00"); // Default 15%
            
            $em->persist($seller);
            $em->flush();

            $this->addFlash("success", "Registration submitted! Your account will be reviewed by our team.");
            return $this->redirectToRoute("app_login");
        }

        return $this->render("seller/register.html.twig");
    }

    #[Route("/seller/dashboard", name: "seller_dashboard")]
    public function dashboard(): Response
    {
        $this->denyAccessUnlessGranted("ROLE_SELLER");
        
        return $this->render("seller/dashboard.html.twig");
    }
}